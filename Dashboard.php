<?php
/* $Id: Dashboard.php 6843 2014-08-20 06:04:47Z daintree $*/

include('includes/session.inc');

$Title = _('Dashboard');

include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/gl.png" title="' . _('Dashboard') . '" alt="" />' . _('Dashboard') . '</p>';

$sql = "SELECT pagesecurity
		FROM scripts
		WHERE scripts.script = 'AgedDebtors.php'";
$ErrMsg = _('The security for Aging Debtors cannot be retrieved because');
$DbgMsg = _('The SQL that was used and failed was');
$Security1Result = DB_query($sql, $ErrMsg, $DbgMsg);
$MyUserRow = DB_fetch_array($Security1Result);
$DebtorSecurity = $MyUserRow['pagesecurity'];

$sql = "SELECT pagesecurity
		FROM scripts
		WHERE scripts.script = 'SuppPaymentRun.php'";
$ErrMsg = _('The security for upcoming payments cannot be retrieved because');
$DbgMsg = _('The SQL that was used and failed was');
$Security2Result = DB_query($sql, $ErrMsg, $DbgMsg);
$MyUserRow = DB_fetch_array($Security2Result);
$PayeeSecurity = $MyUserRow['pagesecurity'];

$sql = "SELECT pagesecurity
		FROM scripts
		WHERE scripts.script = 'GLAccountInquiry.php'";
$ErrMsg = _('The security for G/L Accounts cannot be retrieved because');
$DbgMsg = _('The SQL that was used and failed was');
$Security2Result = DB_query($sql, $ErrMsg, $DbgMsg);
$MyUserRow = DB_fetch_array($Security2Result);
$CashSecurity = $MyUserRow['pagesecurity'];

$sql = "SELECT pagesecurity
		FROM scripts
		WHERE scripts.script = 'SelectSalesOrder.php'";
$ErrMsg = _('The security for Aging Debtors cannot be retrieved because');
$DbgMsg = _('The SQL that was used and failed was');
$Security1Result = DB_query($sql, $ErrMsg, $DbgMsg);
$MyUserRow = DB_fetch_array($Security1Result);
$OrderSecurity = $MyUserRow['pagesecurity'];

if (in_array($DebtorSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($DebtorSecurity)) {
	echo '<br /><b>' . _('Overdue Customer Balances') . '</b>
			<table class="selection"><tbody>';

	$TableHeader = '<tr>
						<th>' . _('Customer') . '</th>
						<th>' . _('Reference') . '</th>
						<th>' . _('Trans Date') . '</th>
						<th>' . _('Due Date') . '</th>
						<th>' . _('Balance') . '</th>
						<th>' . _('Current') . '</th>
						<th>' . _('Due Now') . '</th>
						<th>' . '> ' . $_SESSION['PastDueDays1'] . ' ' . _('Days Over') . '</th>
						<th>' . '> ' . $_SESSION['PastDueDays2'] . ' ' . _('Days Over') . '</th>
					</tr>';
	echo $TableHeader;
	$j = 1;
	$k=0; //row colour counter
	if (!isset($_POST['Salesman'])){
		$_POST['Salesman']='';
	}
	if ($_SESSION['SalesmanLogin'] != '') {
		$_POST['Salesman'] = $_SESSION['SalesmanLogin'];
	}
	if (trim($_POST['Salesman'])!=''){
		$SalesLimit = " AND debtorsmaster.debtorno IN (SELECT DISTINCT debtorno FROM custbranch WHERE salesman = '".$_POST['Salesman']."') ";
	} else {
		$SalesLimit = '';
	}
	$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				currencies.currency,
				currencies.decimalplaces,
				paymentterms.terms,
				debtorsmaster.creditlimit,
				holdreasons.dissallowinvoices,
				holdreasons.reasondescription,
				SUM(
					debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
				) AS balance,
				SUM(
					CASE WHEN (paymentterms.daysbeforedue > 0)
					THEN
						CASE WHEN (TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate)) >= paymentterms.daysbeforedue
						THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
						ELSE 0 END
					ELSE
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))', 'DAY') .")) >= 0
						THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
					END
				) AS due,
				SUM(
					CASE WHEN (paymentterms.daysbeforedue > 0)
					THEN
						CASE WHEN (TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate)) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ")
						THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
					ELSE
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL ('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays1'] . "
						THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
						ELSE 0 END
					END
				) AS overdue1,
				SUM(
					CASE WHEN (paymentterms.daysbeforedue > 0)
					THEN
						CASE WHEN (TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate)) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ")
						THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
					ELSE
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL ('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays2'] . "
						THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
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
					ROUND(ABS(SUM(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc)),currencies.decimalplaces) > 0";
	$CustomerResult = DB_query($SQL,'','',False,False); /*dont trap errors handled below*/

	if (DB_error_no() !=0) {
		prnMsg(_('The customer details could not be retrieved by the SQL because') . ' ' . DB_error_msg(),'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($debug==1){
			echo '<br />' . $SQL;
		}
		include('includes/footer.inc');
		exit;
	}


	$TotBal=0;
	$TotCurr=0;
	$TotDue=0;
	$TotOD1=0;
	$TotOD2=0;

 	$ListCount = DB_num_rows($CustomerResult);
	$CurrDecimalPlaces =2; //by default

	while ($AgedAnalysis = DB_fetch_array($CustomerResult,$db)){
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		$CurrDecimalPlaces = $AgedAnalysis['decimalplaces'];
		$DisplayDue = locale_number_format($AgedAnalysis['due']-$AgedAnalysis['overdue1'],$CurrDecimalPlaces);
		$DisplayCurrent = locale_number_format($AgedAnalysis['balance']-$AgedAnalysis['due'],$CurrDecimalPlaces);
		$DisplayBalance = locale_number_format($AgedAnalysis['balance'],$CurrDecimalPlaces);
		$DisplayOverdue1 = locale_number_format($AgedAnalysis['overdue1']-$AgedAnalysis['overdue2'],$CurrDecimalPlaces);
		$DisplayOverdue2 = locale_number_format($AgedAnalysis['overdue2'],$CurrDecimalPlaces);
		if ($DisplayDue <> 0 OR $DisplayOverdue1 <> 0 OR $DisplayOverdue2 <> 0) {
			$TotBal += $AgedAnalysis['balance'];
			$TotDue += ($AgedAnalysis['due']-$AgedAnalysis['overdue1']);
			$TotCurr += ($AgedAnalysis['balance']-$AgedAnalysis['due']);
			$TotOD1 += ($AgedAnalysis['overdue1']-$AgedAnalysis['overdue2']);
			$TotOD2 += $AgedAnalysis['overdue2'];


			printf('<td><b>%s</b></td>
					<td><b>%s</b></td>
					<td><b>%s</b></td>
					<td><b>%s</b></td>
					<td class="number"><b>%s</b></td>
					<td class="number"><b>%s</b></td>
					<td class="number" style="color:orange;"><b>%s</b></td>
					<td class="number" style="color:red;"><b>%s</b></td>
					<td class="number" style="color:red;"><b>%s</b></td>
					</tr>',
					$AgedAnalysis['debtorno'] . ' - ' . $AgedAnalysis['name'],
					'',
					'',
					'',
					$DisplayBalance,
					$DisplayCurrent,
					$DisplayDue,
					$DisplayOverdue1,
					$DisplayOverdue2 );

			$sql = "SELECT systypes.typename,
						debtortrans.transno,
						debtortrans.trandate,
						daysbeforedue,
						dayinfollowingmonth,
						(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc) as balance,
						(CASE WHEN (paymentterms.daysbeforedue > 0)
							THEN
								(CASE WHEN (TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate)) >= paymentterms.daysbeforedue
								THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
								ELSE 0 END)
							ELSE
								(CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))', 'DAY') . ")) >= 0
								THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
								ELSE 0 END)
						END) AS due,
						(CASE WHEN (paymentterms.daysbeforedue > 0)
							THEN
								(CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ") THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END)
							ELSE
								(CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays1'] . ")
								THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
								ELSE 0 END)
						END) AS overdue1,
						(CASE WHEN (paymentterms.daysbeforedue > 0)
							THEN
								(CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ")
								THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
								ELSE 0 END)
							ELSE
								(CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))','DAY') . ")) >= " . $_SESSION['PastDueDays2'] . ")
								THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
								ELSE 0 END)
						END) AS overdue2
				   FROM debtorsmaster,
						paymentterms,
						debtortrans,
						systypes
				   WHERE systypes.typeid = debtortrans.type
						AND debtorsmaster.paymentterms = paymentterms.termsindicator
						AND debtorsmaster.debtorno = debtortrans.debtorno
						AND debtortrans.debtorno = '" . $AgedAnalysis['debtorno'] . "'
						AND ABS(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc)>0.004";

			if ($_SESSION['SalesmanLogin'] != '') {
				$sql .= " AND debtortrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
			}

			$DetailResult = DB_query($sql,'','',False,False);
			if (DB_error_no() !=0) {
				prnMsg(_('The details of outstanding transactions for customer') . ' - ' . $AgedAnalysis['debtorno'] . ' ' . _('could not be retrieved because') . ' - ' . DB_error_msg(),'error');
				echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
				if ($debug==1){
					echo '<br />' . _('The SQL that failed was') . '<br />' . $sql;
				}
				include('includes/footer.inc');
				exit;
			}

			while ($DetailTrans = DB_fetch_array($DetailResult)){

				$DisplayTranDate = ConvertSQLDate($DetailTrans['trandate']);
				$DisplayDue = locale_number_format($DetailTrans['due']-$DetailTrans['overdue1'],$CurrDecimalPlaces);
				$DisplayCurrent = locale_number_format($DetailTrans['balance']-$DetailTrans['due'],$CurrDecimalPlaces);
				$DisplayBalance = locale_number_format($DetailTrans['balance'],$CurrDecimalPlaces);
				$DisplayOverdue1 = locale_number_format($DetailTrans['overdue1']-$DetailTrans['overdue2'],$CurrDecimalPlaces);
				$DisplayOverdue2 = locale_number_format($DetailTrans['overdue2'],$CurrDecimalPlaces);

				if ($DetailTrans['daysbeforedue'] > 0) {
					$AddDays=$DetailTrans['daysbeforedue'] . ' days';
					if (function_exists(date_add)) {
						$DisplayDueDate = date_add(date_create($DetailTrans['trandate']), date_interval_create_from_date_string($AddDays));
					} else {
				 		$DisplayDueDate = strtotime($AddDays,strtotime($DetailTrans['trandate']));
					}

				} else {
					$AddDays=(intval($DetailTrans['dayinfollowingmonth']) - 1) . ' days';
					if (function_exists(date_add)){
						$DisplayDueDate = date_create($DetailTrans['trandate']);
						$DisplayDueDate->modify('first day of next month');
						$DisplayDueDate = date_add($DisplayDueDate, date_interval_create_from_date_string($AddDays));
					} else {
						$DisplayDueDate = strtotime('first day of next month',strtotime($DetailTrans['trandate']));
						$DisplayDueDate = strtotime($DisplayDueDate,strtotime($AddDays));
					}

				}
				if (function_exists(date_add)) {
					$DisplayDueDate=date_format($DisplayDueDate,$_SESSION['DefaultDateFormat']);
				} else {
					$DisplayDueDate = Date($_SESSION['DefaultDateFormat'],$DisplayDueDate);
				}
				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k=1;
				}
				printf('<td style="text-align:center">%s</td>
					<td style="text-align:right">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number" style="color:orange;">%s</td>
					<td class="number" style="color:red;">%s</td>
					<td class="number" style="color:red;">%s</td>',
					_($DetailTrans['typename']),
					$DetailTrans['transno'],
					$DisplayTranDate,
					$DisplayDueDate,
					$DisplayBalance,
					$DisplayCurrent,
					$DisplayDue,
					$DisplayOverdue1,
					$DisplayOverdue2);
				echo '</tr>';
			} //end while there are detail transactions to show
		} //has Due now or overdue
	} //end customer aged analysis while loop

	$DisplayTotBalance = locale_number_format($TotBal,$CurrDecimalPlaces);
	$DisplayTotDue = locale_number_format($TotDue,$CurrDecimalPlaces);
	$DisplayTotCurrent = locale_number_format($TotCurr,$CurrDecimalPlaces);
	$DisplayTotOverdue1 = locale_number_format($TotOD1,$CurrDecimalPlaces);
	$DisplayTotOverdue2 = locale_number_format($TotOD2,$CurrDecimalPlaces);
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}
	printf('<td style="text-align:left"><b>%s</b></td>
				<td><b>%s</b></td>
				<td><b>%s</b></td>
				<td><b>%s</b></td>
				<td class="number"><b>%s</b></td>
				<td class="number"><b>%s</b></td>
				<td class="number"><b>%s</b></td>
				<td class="number" style="color:red;"><b>%s</b></td>
				<td class="number" style="color:red;"><b>%s</b></td>',
				_('Totals'),
				'',
				'',
				'',
				$DisplayTotBalance,
				$DisplayTotCurrent,
				$DisplayTotDue,
				$DisplayTotOverdue1,
				$DisplayTotOverdue2);

	echo '</tr>';
	echo '</tbody>
		</table>';
} //DebtorSecurity

if (in_array($PayeeSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($PayeeSecurity)) {

	echo '<br /><b>' . _('Supplier Invoices Due within 1 Month') . '</b>
			<table class="selection">
				<tbody>
				<tr>
					<th>' . _('Supplier') . '</th>
					<th>' . _('Invoice Date') . '</th>
					<th>' . _('Invoice') . '</th>
					<th>' . _('Amount Due') . '</th>
					<th>' . _('Due Date') . '</th>
				</tr>';

	$sql = "SELECT suppliers.supplierid,
					currencies.decimalplaces AS currdecimalplaces,
					SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance
			FROM suppliers INNER JOIN paymentterms
			ON suppliers.paymentterms = paymentterms.termsindicator
			INNER JOIN supptrans
			ON suppliers.supplierid = supptrans.supplierno
			INNER JOIN systypes
			ON systypes.typeid = supptrans.type
			INNER JOIN currencies
			ON suppliers.currcode=currencies.currabrev
			WHERE supptrans.ovamount + supptrans.ovgst - supptrans.alloc !=0
			AND supptrans.hold=0
			GROUP BY suppliers.supplierid,
					currencies.decimalplaces
			HAVING SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) <> 0
			ORDER BY suppliers.supplierid";

	$SuppliersResult = DB_query($sql);

	$SupplierID ='';
	$TotalPayments = 0;
	$TotalAccumDiffOnExch = 0;

	while ($SuppliersToPay = DB_fetch_array($SuppliersResult)){

		$CurrDecimalPlaces = $SuppliersToPay['currdecimalplaces'];

		$sql = "SELECT suppliers.supplierid,
						suppliers.suppname,
						systypes.typename,
						paymentterms.terms,
						supptrans.suppreference,
						supptrans.trandate,
						supptrans.rate,
						supptrans.transno,
						supptrans.type,
						supptrans.duedate,
						(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance,
						(supptrans.ovamount + supptrans.ovgst ) AS trantotal,
						supptrans.diffonexch,
						supptrans.id
				FROM suppliers INNER JOIN paymentterms
				ON suppliers.paymentterms = paymentterms.termsindicator
				INNER JOIN supptrans
				ON suppliers.supplierid = supptrans.supplierno
				INNER JOIN systypes
				ON systypes.typeid = supptrans.type
				WHERE supptrans.supplierno = '" . $SuppliersToPay['supplierid'] . "'
				AND supptrans.ovamount + supptrans.ovgst - supptrans.alloc !=0
				AND supptrans.duedate <='" . Date('Y-m-d', mktime(0,0,0, Date('n'),Date('j')+30,date('Y'))) . "'
				AND supptrans.hold = 0
				ORDER BY supptrans.supplierno,
					supptrans.type,
					supptrans.transno";

		$TransResult = DB_query($sql,'','',false,false);
		if (DB_error_no() !=0) {
			prnMsg(_('The details of supplier invoices due could not be retrieved because') . ' - ' . DB_error_msg(),'error');
			echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
			if ($debug==1){
				echo '<br />' . _('The SQL that failed was') . ' ' . $sql;
			}
			include('includes/footer.inc');
			exit;
		}

		unset($Allocs);
		$Allocs = array();
		$AllocCounter =0;
		$AccumBalance =0;
		$k=0; //row colour counter

		while ($DetailTrans = DB_fetch_array($TransResult)){

			if ($DetailTrans['supplierid'] != $SupplierID){ /*Need to head up for a new suppliers details */
				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k=1;
				}
				$SupplierID = $DetailTrans['supplierid'];
				$SupplierName = $DetailTrans['suppname'];

				//$AccumBalance = 0;
				$AccumDiffOnExch = 0;

				printf('<td style="text-align:left"><b>%s</b></td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>',
						$DetailTrans['supplierid'] . ' - ' . $DetailTrans['suppname'] . ' - ' . $DetailTrans['terms'],
						'',
						'',
						'',
						'');
				echo '</tr>';
			}
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			$DisplayFormat = '';

			if ((time()-(60*60*24)) > strtotime($DetailTrans['duedate'])) {
				$DisplayFormat=' style="color:red;" ';
			}
			$DislayTranDate = ConvertSQLDate($DetailTrans['trandate']);
			$AccumBalance += $DetailTrans['balance'];
			$PayNow='<a href="' . $RootPath . '/Payments.php?&SupplierID=' . $SupplierID. '&amp;Amount=' . $DetailTrans['balance'] . '&amp;BankTransRef='  .$DetailTrans['suppreference'] . '">' .$DetailTrans['suppreference'] . '</a>';
			printf('<td style="text-align:center">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number"' . $DisplayFormat . '>%s</td>
					<td' . $DisplayFormat . '>%s</td>',
					_($DetailTrans['typename']),
					$DislayTranDate,
					$PayNow,
					locale_number_format($DetailTrans['balance'],$CurrDecimalPlaces),
					ConvertSQLDate($DetailTrans['duedate']));
			echo '</tr>';
		} /*end while there are detail transactions to show */
	} /* end while there are suppliers to retrieve transactions for */

	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}
	echo '<td style="text-align:left">' . _('Grand Total Payments Due') . '</td>
			<td></td>
			<td></td>
			<td class="number"><b>' . locale_number_format($AccumBalance,$CurrDecimalPlaces) . '</b></td>
			<td></td>
		</tr>
		</tbody>
		</table>';
}  //PayeeSecurity
if (in_array($CashSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($CashSecurity)) {
	include('includes/GLPostings.inc');
	echo '<br /><b>' . _('Bank and Credit Card Balances') . '</b>
			<table class="selection"><tbody>';
	$FirstPeriodSelected = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);
	$LastPeriodSelected = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);
	$SelectedPeriod=$LastPeriodSelected;
	echo '<tr>
				<th class="ascending">' . _('GL Account') . '</th>
				<th class="ascending">' . _('Account Name') . '</th>
				<th class="ascending">' . _('Balance') . '</th>
			</tr>';

	$sql = "SELECT bankaccounts.accountcode,
					bankaccounts.bankaccountcode,
					chartmaster.accountname,
					bankaccountname
			FROM bankaccounts INNER JOIN chartmaster
			ON bankaccounts.accountcode = chartmaster.accountcode";

	$ErrMsg = _('The bank accounts set up could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the bank account details was') . '<br />' . $sql;
	$result1 = DB_query($sql,$ErrMsg,$DbgMsg);

	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result1)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}
		/*Is the account a balance sheet or a profit and loss account */
		$result = DB_query("SELECT pandl
						FROM accountgroups
						INNER JOIN chartmaster ON accountgroups.groupname=chartmaster.group_
						WHERE chartmaster.accountcode='" . $myrow['accountcode'] ."'");
		$PandLRow = DB_fetch_row($result);
		if ($PandLRow[0]==1){
			$PandLAccount = True;
		}else{
			$PandLAccount = False; /*its a balance sheet account */
		}

		$sql= "SELECT counterindex,
						type,
						typename,
						gltrans.typeno,
						trandate,
						narrative,
						amount,
						periodno,
						gltrans.tag,
						tagdescription
					FROM gltrans INNER JOIN systypes
					ON systypes.typeid=gltrans.type
					LEFT JOIN tags
					ON gltrans.tag = tags.tagref
					WHERE gltrans.account = '" . $myrow['accountcode'] . "'
					AND posted=1
					AND periodno>='" . $FirstPeriodSelected . "'
					AND periodno<='" . $LastPeriodSelected . "'
					ORDER BY periodno, gltrans.trandate, counterindex";
		$TransResult = DB_query($sql,$ErrMsg);
		if ($PandLAccount==True) {
			$RunningTotal = 0;
		} else {
				// added to fix bug with Brought Forward Balance always being zero
			$sql = "SELECT bfwd,
						actual,
						period
					FROM chartdetails
					WHERE chartdetails.accountcode='" . $myrow['accountcode'] . "'
					AND chartdetails.period='" . $FirstPeriodSelected . "'";

			$ErrMsg = _('The chart details for account') . ' ' . $myrow['accountcode'] . ' ' . _('could not be retrieved');
			$ChartDetailsResult = DB_query($sql,$ErrMsg);
			$ChartDetailRow = DB_fetch_array($ChartDetailsResult);
			$RunningTotal =$ChartDetailRow['bfwd'];
		}
		$PeriodTotal = 0;
		$PeriodNo = -9999;
		while ($myrow2=DB_fetch_array($TransResult)) {
			if ($myrow2['periodno']!=$PeriodNo){
				if ($PeriodNo!=-9999){ //ie its not the first time around
					/*Get the ChartDetails balance b/fwd and the actual movement in the account for the period as recorded in the chart details - need to ensure integrity of transactions to the chart detail movements. Also, for a balance sheet account it is the balance carried forward that is important, not just the transactions*/

					$sql = "SELECT bfwd,
							actual,
							period
						FROM chartdetails
						WHERE chartdetails.accountcode='" . $myrow['accountcode'] . "'
						AND chartdetails.period='" . $PeriodNo . "'";

					$ErrMsg = _('The chart details for account') . ' ' . $myrow['accountcode'] . ' ' . _('could not be retrieved');
					$ChartDetailsResult = DB_query($sql,$ErrMsg);
					$ChartDetailRow = DB_fetch_array($ChartDetailsResult);
					if ($PeriodTotal < 0 ){ //its a credit balance b/fwd
						if ($PandLAccount==True) {
							$RunningTotal = 0;
						}
					} else { //its a debit balance b/fwd
						if ($PandLAccount==True) {
							$RunningTotal = 0;
						}
					}
				}
				$PeriodNo = $myrow2['periodno'];
				$PeriodTotal = 0;
			}
			$RunningTotal += $myrow2['amount'];
			$PeriodTotal += $myrow2['amount'];
		}
		$DisplayBalance=locale_number_format(($RunningTotal),$_SESSION['CompanyRecord']['decimalplaces']);
		printf('<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>',
				$myrow['accountcode'] . ' - ' . $myrow['accountname'],
				$myrow['bankaccountname'],
				$DisplayBalance);
	} //each bank account
	echo '</tbody>
		</table>';
} //CashSecurity

if (in_array($OrderSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($OrderSecurity)) {
	echo '<br /><b>' . _('Outstanding Orders') . '</b>
			<table cellpadding="2" width="95%" class="selection">
			<tr>
				<th>' . _('View Order') . '</th>
				<th>' . _('Customer') . '</th>
				<th>' . _('Branch') . '</th>
				<th>' . _('Cust Order') . ' #</th>
				<th>' . _('Order Date') . '</th>
				<th>' . _('Req Del Date') . '</th>
				<th>' . _('Delivery To') . '</th>
				<th>' . _('Order Total') . ' ' . _('in') . ' ' . $_SESSION['CompanyRecord']['currencydefault'] . '</th>
			</tr>';

	$SQL = "SELECT salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate,
						salesorders.printedpackingslip,
						salesorders.poplaced,
						SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)/currencies.rate) AS ordervalue
					FROM salesorders INNER JOIN salesorderdetails
						ON salesorders.orderno = salesorderdetails.orderno
						INNER JOIN debtorsmaster
						ON salesorders.debtorno = debtorsmaster.debtorno
						INNER JOIN custbranch
						ON debtorsmaster.debtorno = custbranch.debtorno
						AND salesorders.branchcode = custbranch.branchcode
						INNER JOIN currencies
						ON debtorsmaster.currcode = currencies.currabrev
					WHERE salesorderdetails.completed=0
					AND salesorders.quotation =0
					GROUP BY salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate,
						salesorders.printedpackingslip
					ORDER BY salesorders.orddate DESC, salesorders.orderno";
	$ErrMsg = _('No orders or quotations were returned by the SQL because');
	$SalesOrdersResult = DB_query($SQL,$ErrMsg);

	/*show a table of the orders returned by the SQL */
	if (DB_num_rows($SalesOrdersResult)>0) {
		$k=0; //row colour counter
		$OrdersTotal =0;
		$FontColor='';

		while ($myrow=DB_fetch_array($SalesOrdersResult)) {

			$OrderDate = ConvertSQLDate($myrow['orddate']);
			$FormatedDelDate = ConvertSQLDate($myrow['deliverydate']);
			$FormatedOrderValue = locale_number_format($myrow['ordervalue'],$_SESSION['CompanyRecord']['decimalplaces']);

			if (DateDiff(Date($_SESSION['DefaultDateFormat']),$OrderDate,'d')>5) {
				$FontColor=' style="color:green; font-weight:bold"';
			}
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}

			printf(	'<td><a href="%s" target="_blank">' . $myrow['orderno'] . '</a></td>
					<td' . $FontColor . '>%s</td>
					<td' . $FontColor . '>%s</td>
					<td' . $FontColor . '>%s</td>
					<td' . $FontColor . '>%s</td>
					<td' . $FontColor . '>%s</td>
					<td' . $FontColor . '>%s</td>
					<td' . $FontColor . ' class="number">%s</td>
					</tr>',
					$RootPath . '/OrderDetails.php?OrderNumber=' . $myrow['orderno'],
					$myrow['name'],
					$myrow['brname'],
					$myrow['customerref'],
					$OrderDate,
					$FormatedDelDate,
					html_entity_decode($myrow['deliverto'],ENT_QUOTES,'UTF-8'),
					$FormatedOrderValue);
			$OrdersTotal += $myrow['ordervalue'];
		} //while

		echo '<tfoot>
				<tr>
					<td colspan="7" class="number"><b>' . _('Total Order(s) Value in') . ' ' . $_SESSION['CompanyRecord']['currencydefault'] . ' :</b></td>
					<td class="number"><b>' . locale_number_format($OrdersTotal,$_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
				</tr>
			</tfoot>
			</table>';
	} //rows > 0
} //OrderSecurity
include('includes/footer.inc');
?>

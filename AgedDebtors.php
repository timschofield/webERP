<?php

 /* $Id: AgedDebtors.php 6503 2013-12-19 16:16:41Z exsonqu $ */

include('includes/session.inc');

if (isset($_POST['PrintPDF'])
	and isset($_POST['FromCriteria'])
	and mb_strlen($_POST['FromCriteria'])>=1
	and isset($_POST['ToCriteria'])
	and mb_strlen($_POST['ToCriteria'])>=1) {

	include('includes/PDFStarter.php');
	$pdf->addInfo('Title',_('Aged Customer Balance Listing'));
	$pdf->addInfo('Subject',_('Aged Customer Balances'));
	$FontSize = 12;
	$PageNumber = 0;
	$line_height = 12;

	  /*Now figure out the aged analysis for the customer range under review */
	if ($_SESSION['SalesmanLogin'] != '') {
		$_POST['Salesman'] = $_SESSION['SalesmanLogin'];
	}
	if (trim($_POST['Salesman'])!=''){
		$SalesLimit = " AND debtorsmaster.debtorno IN (SELECT DISTINCT debtorno FROM custbranch WHERE salesman = '".$_POST['Salesman']."') ";
	} else {
		$SalesLimit = "";
	}
	if ($_POST['All_Or_Overdues']=='All'){
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
					ROUND(ABS(SUM(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc)),currencies.decimalplaces) > 0";

	} elseif ($_POST['All_Or_Overdues']=='OverduesOnly') {

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
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= paymentterms.daysbeforedue
							THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
							ELSE 0 END
						ELSE
						CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))', 'DAY') . ")) >= 0 )
						THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
					END
				) AS due,
			SUM(
			  		CASE WHEN (paymentterms.daysbeforedue > 0)
						THEN
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ")
							THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
							ELSE 0 END
						ELSE
						CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays1'] . ")
							THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
							ELSE 0 END
					END
				) AS overdue1,
			SUM(
					CASE WHEN (paymentterms.daysbeforedue > 0)
						THEN
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ")
							THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
							ELSE 0 END
						ELSE
						CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays2'] . ")
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
							THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
							ELSE 0 END
						ELSE
						CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays1'] . ")
							THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
							ELSE 0 END
					END
					) > 0.01";

	} elseif ($_POST['All_Or_Overdues']=='HeldOnly'){

		$SQL = "SELECT debtorsmaster.debtorno,
					debtorsmaster.name,
					currencies.currency,
					currencies.decimalplaces,
					paymentterms.terms,
					debtorsmaster.creditlimit,
					holdreasons.dissallowinvoices,
					holdreasons.reasondescription,
					SUM(debtortrans.ovamount +
						debtortrans.ovgst +
						debtortrans.ovfreight +
						debtortrans.ovdiscount -
						debtortrans.alloc) AS balance,
					SUM(
						CASE WHEN (paymentterms.daysbeforedue > 0)
							THEN
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= paymentterms.daysbeforedue
								THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
								ELSE 0 END
							ELSE
								CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate," . INTERVAL('1', 'MONTH') . ")," .  INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))', 'DAY') . ")) >= 0)
								THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
								ELSE 0 END
						END
					) AS due,
					SUM(
						CASE WHEN (paymentterms.daysbeforedue > 0)
							THEN
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
								AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ")
								THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
							ELSE
								CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays1'] . ")
								THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
							ELSE 0 END
						END
					) AS overdue1,
					SUM(
						CASE WHEN (paymentterms.daysbeforedue > 0)
							THEN
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
								AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ")
								THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
								ELSE 0 END
							ELSE
								CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))', 'DAY') . ")) >= ".$_SESSION['PastDueDays2'] . ")
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
				HAVING ABS(SUM(
					debtortrans.ovamount +
					debtortrans.ovgst +
					debtortrans.ovfreight +
					debtortrans.ovdiscount -
					debtortrans.alloc)) >0.005";
	}
	$CustomerResult = DB_query($SQL,$db,'','',False,False); /*dont trap errors handled below*/

	if (DB_error_no($db) !=0) {
		$Title = _('Aged Customer Account Analysis') . ' - ' . _('Problem Report') . '.... ';
		include('includes/header.inc');
		prnMsg(_('The customer details could not be retrieved by the SQL because') . ' ' . DB_error_msg($db),'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($debug==1){
			echo '<br />' . $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	include ('includes/PDFAgedDebtorsPageHeader.inc');

	$TotBal=0;
	$TotCurr=0;
	$TotDue=0;
	$TotOD1=0;
	$TotOD2=0;

 	$ListCount = DB_num_rows($CustomerResult);
	$CurrDecimalPlaces =2; //by default

	while ($AgedAnalysis = DB_fetch_array($CustomerResult,$db)){
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

		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,220-$Left_Margin,$FontSize,$AgedAnalysis['debtorno'] . ' - ' . $AgedAnalysis['name'],'left');
		$LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayBalance,'right');
		$LeftOvers = $pdf->addTextWrap(280,$YPos,60,$FontSize,$DisplayCurrent,'right');
		$LeftOvers = $pdf->addTextWrap(340,$YPos,60,$FontSize,$DisplayDue,'right');
		$LeftOvers = $pdf->addTextWrap(400,$YPos,60,$FontSize,$DisplayOverdue1,'right');
		$LeftOvers = $pdf->addTextWrap(460,$YPos,60,$FontSize,$DisplayOverdue2,'right');

		$YPos -=$line_height;
		if ($YPos < $Bottom_Margin + $line_height){
			include('includes/PDFAgedDebtorsPageHeader.inc');
		}


		if ($_POST['DetailedReport']=='Yes'){

			/*draw a line under the customer aged analysis*/
			$pdf->line($Page_Width-$Right_Margin, $YPos+10,$Left_Margin, $YPos+10);

			$sql = "SELECT systypes.typename,
						debtortrans.transno,
						debtortrans.trandate,
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

			$DetailResult = DB_query($sql,$db,'','',False,False); /*Dont trap errors */
			if (DB_error_no($db) !=0) {
				$Title = _('Aged Customer Account Analysis') . ' - ' . _('Problem Report') . '....';
				include('includes/header.inc');
				prnMsg(_('The details of outstanding transactions for customer') . ' - ' . $AgedAnalysis['debtorno'] . ' ' . _('could not be retrieved because') . ' - ' . DB_error_msg($db),'error');
				echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
				if ($debug==1){
					echo '<br />' . _('The SQL that failed was') . '<br />' . $sql;
				}
				include('includes/footer.inc');
				exit;
			}

			while ($DetailTrans = DB_fetch_array($DetailResult)){

				$LeftOvers = $pdf->addTextWrap($Left_Margin+5,$YPos,60,$FontSize,$DetailTrans['typename'],'left');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+65,$YPos,60,$FontSize,$DetailTrans['transno'],'left');
				$DisplayTranDate = ConvertSQLDate($DetailTrans['trandate']);
				$LeftOvers = $pdf->addTextWrap($Left_Margin+125,$YPos,75,$FontSize,$DisplayTranDate,'left');

				$DisplayDue = locale_number_format($DetailTrans['due']-$DetailTrans['overdue1'],$CurrDecimalPlaces);
				$DisplayCurrent = locale_number_format($DetailTrans['balance']-$DetailTrans['due'],$CurrDecimalPlaces);
				$DisplayBalance = locale_number_format($DetailTrans['balance'],$CurrDecimalPlaces);
				$DisplayOverdue1 = locale_number_format($DetailTrans['overdue1']-$DetailTrans['overdue2'],$CurrDecimalPlaces);
				$DisplayOverdue2 = locale_number_format($DetailTrans['overdue2'],$CurrDecimalPlaces);

				$LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayBalance,'right');
				$LeftOvers = $pdf->addTextWrap(280,$YPos,60,$FontSize,$DisplayCurrent,'right');
				$LeftOvers = $pdf->addTextWrap(340,$YPos,60,$FontSize,$DisplayDue,'right');
				$LeftOvers = $pdf->addTextWrap(400,$YPos,60,$FontSize,$DisplayOverdue1,'right');
				$LeftOvers = $pdf->addTextWrap(460,$YPos,60,$FontSize,$DisplayOverdue2,'right');

				$YPos -=$line_height;
				if ($YPos < $Bottom_Margin + $line_height){
					$PageNumber++;
					include('includes/PDFAgedDebtorsPageHeader.inc');
				}

			} /*end while there are detail transactions to show */
			$FontSize=8;
			/*draw a line under the detailed transactions before the next customer aged analysis*/
			$pdf->line($Page_Width-$Right_Margin, $YPos+10,$Left_Margin, $YPos+10);
		} /*Its a detailed report */
	} /*end customer aged analysis while loop */

	$YPos -=$line_height;
	if ($YPos < $Bottom_Margin + (2*$line_height)){
		$PageNumber++;
		include('includes/PDFAgedDebtorsPageHeader.inc');
	} elseif ($_POST['DetailedReport']=='Yes') {
		//dont do a line if the totals have to go on a new page
		$pdf->line($Page_Width-$Right_Margin, $YPos+10 ,220, $YPos+10);
	}

	$DisplayTotBalance = locale_number_format($TotBal,$CurrDecimalPlaces);
	$DisplayTotDue = locale_number_format($TotDue,$CurrDecimalPlaces);
	$DisplayTotCurrent = locale_number_format($TotCurr,$CurrDecimalPlaces);
	$DisplayTotOverdue1 = locale_number_format($TotOD1,$CurrDecimalPlaces);
	$DisplayTotOverdue2 = locale_number_format($TotOD2,$CurrDecimalPlaces);

	$LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayTotBalance,'right');
	$LeftOvers = $pdf->addTextWrap(280,$YPos,60,$FontSize,$DisplayTotCurrent,'right');
	$LeftOvers = $pdf->addTextWrap(340,$YPos,60,$FontSize,$DisplayTotDue,'right');
	$LeftOvers = $pdf->addTextWrap(400,$YPos,60,$FontSize,$DisplayTotOverdue1,'right');
	$LeftOvers = $pdf->addTextWrap(460,$YPos,60,$FontSize,$DisplayTotOverdue2,'right');

	if ($ListCount == 0) {
		$Title = _('Aged Customer Account Analysis') . ' - ' . _('Problem Report') . '....';
		include('includes/header.inc');
		prnMsg(_('There are no customers with balances meeting the criteria specified to list'),'info');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		include('includes/footer.inc');
		exit;
	} else {
		$pdf->OutputD($_SESSION['DatabaseName'] . '_' . 'AgedDebtors_' . date('Y-m-d') . '.pdf');
		$pdf-> __destruct();
	}

} else { /*The option to print PDF was not hit */

	$Title=_('Aged Debtor Analysis');

	$ViewTopic = 'ARReports';
	$BookMark = 'AgedDebtors';

	include('includes/header.inc');

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p><br />';

	if ((!isset($_POST['FromCriteria']) or !isset($_POST['ToCriteria']))) {

	/*if $FromCriteria is not set then show a form to allow input	*/

		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">
            <div>
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<table class="selection">
			<tr>
				<td>' . _('From Customer Code') . ':' . '</td>
				<td><input tabindex="1" autofocus="autofocus" required="required" type="text" maxlength="6" size="7" name="FromCriteria" value="0" title="' . _('Enter the first customer code alphabetically to include in the report') . '" /></td>
			</tr>
			<tr>
				<td>' . _('To Customer Code') . ':' . '</td>
				<td><input tabindex="2" type="text" required="required"  maxlength="6" size="7" name="ToCriteria" value="zzzzzz" title="' . _('Enter the last customer code alphabetically to include in the report') . '" /></td>
			</tr>
			<tr>
				<td>' . _('All balances or overdues only') . ':' . '</td>
				<td><select tabindex="3" name="All_Or_Overdues">
					<option selected="selected" value="All">' . _('All customers with balances') . '</option>
					<option value="OverduesOnly">' . _('Overdue accounts only') . '</option>
					<option value="HeldOnly">' . _('Held accounts only') . '</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>' . _('Only Show Customers Of') . ':' . '</td>';
		if ($_SESSION['SalesmanLogin'] != '') {
			echo '<td>';
			echo $_SESSION['UsersRealName'];
			echo '</td>';
		}else{
			echo '<td><select tabindex="4" name="Salesman">';

			$sql = "SELECT salesmancode, salesmanname FROM salesman";

			$result=DB_query($sql,$db);
			echo '<option value="">' . _('All Sales people') . '</option>';
			while ($myrow=DB_fetch_array($result)){
					echo '<option value="' . $myrow['salesmancode'] . '">' . $myrow['salesmanname'] . '</option>';
			}
			echo '</select></td>';
		}
		echo '</tr>
			<tr>
				<td>' . _('Only show customers trading in') . ':' . '</td>
				<td><select tabindex="5" name="Currency">';

		$sql = "SELECT currency, currabrev FROM currencies";

		$result=DB_query($sql,$db);
		while ($myrow=DB_fetch_array($result)){
			  if ($myrow['currabrev'] == $_SESSION['CompanyRecord']['currencydefault']){
				echo '<option selected="selected" value="' . $myrow['currabrev'] . '">' . $myrow['currency'] . '</option>';
			  } else {
				  echo '<option value="' . $myrow['currabrev'] . '">' . $myrow['currency'] . '</option>';
			  }
		}
		echo '</select></td>
			</tr>
			<tr>
				<td>' . _('Summary or detailed report') . ':' . '</td>
				<td><select tabindex="6" name="DetailedReport">
					<option selected="selected" value="No">' . _('Summary Report') . '</option>
					<option value="Yes">' . _('Detailed Report') . '</option>
					</select>
				</td>
			</tr>
			</table>
			<br />
			<div class="centre">
				<input tabindex="7" type="submit" name="PrintPDF" value="' . _('Print PDF') , '" />
			</div>
            </div>
            </form>';
	}
	include('includes/footer.inc');
} /*end of else not PrintPDF */
?>

<?php

/* $Revision: 1.10 $ */

$PageSecurity = 2;

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');

If (isset($_POST['PrintPDF']) && isset($_POST['FromCust']) && $_POST['FromCust']!=''){

	$_POST['FromCust'] = strtoupper($_POST['FromCust']);

	If (!isset($_POST['ToCust'])){
	      $_POST['ToCust'] = $_POST['FromCust'];
	} else {
		$_POST['ToCust'] = strtoupper($_POST['ToCust']);
	}

	include('includes/PDFStarter.php');

	$pdf->addinfo('Title', _('Customer Statements') );
	$pdf->addinfo('Subject', _('Statements from') . ' ' . $_POST['FromCust'] . ' ' . _('to').' ' . $_POST['ToCust']);
	$PageNumber = 1;


	$line_height=16;

	$FirstStatement = True;

/* Do a quick tidy up to settle any transactions that should have been settled at the time of allocation but for whatever reason weren't */
	$ErrMsg = _('There was a problem settling the old transactions.');
	$DbgMsg = _('The SQL used to settle outstanding transactions was');
	$sql = "UPDATE debtortrans SET settled=1
		WHERE ABS(debtortrans.ovamount+debtortrans.ovdiscount+debtortrans.ovfreight+debtortrans.ovgst-debtortrans.alloc)<0.009";
	$SettleAsNec = DB_query($sql,$db, $ErrMsg, $DbgMsg);

/*Figure out who all the customers in this range are */
	$ErrMsg= _('There was a problem retrieving the customer information for the statements from the database');
	$sql = "SELECT debtorsmaster.debtorno,
			debtorsmaster.name,
			debtorsmaster.address1,
			debtorsmaster.address2,
			debtorsmaster.address3,
			debtorsmaster.address4,
			debtorsmaster.address5,
			debtorsmaster.address6,
			debtorsmaster.lastpaid,
			debtorsmaster.lastpaiddate,
			currencies.currency,
			paymentterms.terms
		FROM debtorsmaster INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
		INNER JOIN paymentterms
			ON debtorsmaster.paymentterms=paymentterms.termsindicator
		WHERE debtorsmaster.debtorno >='" . $_POST['FromCust'] ."'
		AND debtorsmaster.debtorno <='" . $_POST['ToCust'] ."'
		ORDER BY debtorsmaster.debtorno";
	$StatementResults=DB_query($sql,$db, $ErrMsg);

	if (DB_Num_Rows($StatementResults) == 0){
		$title = _('Print Statments') . ' - ' . _('No Customers Found');
	        require('includes/header.inc');
		echo '<div align=center><br><br><br>';
		prnMsg( _('There were no Customers matching your selection of '). $_POST['FromCust']. ' - '.
			$_POST['ToCust'].'.' , 'error');
		echo '</div>';
		include('includes/footer.inc');
		exit();
	}

	while ($StmtHeader=DB_fetch_array($StatementResults)){	 /*loop through all the customers returned */

	/*now get all the outstanding transaction ie Settled=0 */
		$ErrMsg =  _('There was a problem retrieving the outstanding transactions for') . ' ' .	$StmtHeader['name'] . ' '. _('from the database') . '.';
		$sql = "SELECT systypes.typename,
				debtortrans.transno,
				debtortrans.trandate,
				debtortrans.ovamount+debtortrans.ovdiscount+debtortrans.ovfreight+debtortrans.ovgst as total,
				debtortrans.alloc,
				debtortrans.ovamount+debtortrans.ovdiscount+debtortrans.ovfreight+debtortrans.ovgst-debtortrans.alloc as ostdg
			FROM debtortrans INNER JOIN systypes
				ON debtortrans.type=systypes.typeid
			WHERE debtortrans.debtorno='" . $StmtHeader['debtorno'] . "'
			AND debtortrans.settled=0
			ORDER BY debtortrans.id";

		$OstdgTrans=DB_query($sql,$db, $ErrMsg);

	   	$NumberOfRecordsReturned = DB_num_rows($OstdgTrans);

/*now get all the settled transactions which were allocated this month */
		$ErrMsg = _('There was a problem retrieving the transactions that were settled over the course of the last month for'). ' ' . $StmtHeader['name'] . ' ' . _('from the database');
	   	if ($_SESSION['Show_Settled_LastMonth']==1){
	   		$sql = "SELECT DISTINCT debtortrans.id,
						systypes.typename,
						debtortrans.transno,
						debtortrans.trandate,
						debtortrans.ovamount+debtortrans.ovdiscount+debtortrans.ovfreight+debtortrans.ovgst AS total,
						debtortrans.alloc,
						debtortrans.ovamount+debtortrans.ovdiscount+debtortrans.ovfreight+debtortrans.ovgst-debtortrans.alloc AS ostdg
				FROM debtortrans INNER JOIN systypes
					ON debtortrans.type=systypes.typeid
				INNER JOIN custallocns
					ON (debtortrans.id=custallocns.transid_allocfrom
						OR debtortrans.id=custallocns.transid_allocto)
				WHERE custallocns.datealloc >='" .
					Date('Y-m-d',Mktime(0,0,0,Date('m')-1,Date('d'),Date('y'))) . "'
				AND debtortrans.debtorno='" . $StmtHeader['debtorno'] . "' 
				AND debtortrans.settled=1
				ORDER BY debtortrans.id";

			$SetldTrans=DB_query($sql,$db, $ErrMsg);
			$NumberOfRecordsReturned += DB_num_rows($SetldTrans);
	   	}

	  	if ( $NumberOfRecordsReturned >=1){

		/* Then there's a statement to print. So print out the statement header from the company record */

	      		$PageNumber =1;

			if ($FirstStatement==True){
				$FirstStatement=False;
	      		} else {
				$pdf->newPage();
	      		}

	      		include('includes/PDFStatementPageHeader.inc');


			if ($_SESSION['Show_Settled_LastMonth']==1){
				if (DB_num_rows($SetldTrans)>=1) {

					$FontSize=12;
					$YPos -= $line_height;
					$pdf->addText($Left_Margin+1,$YPos,$FontSize, _('Settled Transactions'));

					$YPos -= ($line_height);

					$FontSize=10;

					while ($myrow=DB_fetch_array($SetldTrans)){

						$DisplayAlloc = number_format($myrow['alloc'],2);
						$DisplayOutstanding = number_format($myrow['ostdg'],2);

						$FontSize=9;

						$LeftOvers = $pdf->addTextWrap($Left_Margin+1,$YPos,60,$FontSize,$myrow['typename'], 'left');
						$LeftOvers = $pdf->addTextWrap($Left_Margin+61,$YPos,50,$FontSize,$myrow['transno'], 'left');
						$LeftOvers = $pdf->addTextWrap($Left_Margin+111,$YPos,50,$FontSize,ConvertSQLDate($myrow['trandate']), 'left');

						$FontSize=10;
						if ($myrow['total']>0){
							$DisplayTotal = number_format($myrow['total'],2);
							$LeftOvers = $pdf->addTextWrap($Left_Margin+150,$YPos,60,$FontSize,$DisplayTotal, 'right');
						} else {
							$DisplayTotal = number_format(-$myrow['total'],2);
							$LeftOvers = $pdf->addTextWrap($Left_Margin+210,$YPos,60,$FontSize,$DisplayTotal, 'right');
						}
						$LeftOvers = $pdf->addTextWrap($Left_Margin+270,$YPos,60,$FontSize,$DisplayAlloc, 'right');
						$LeftOvers = $pdf->addTextWrap($Left_Margin+330,$YPos,60,$FontSize,$DisplayOutstanding, 'right');

						if ($YPos-$line_height <= $Bottom_Margin){
		/* head up a new statement page */

							$PageNumber++;
							$pdf->newPage();
							include ('includes/PDFStatementPageHeader.inc');
						} //end if need a new page headed up

						/*increment a line down for the next line item */
						$YPos -= ($line_height);

					} //end while there transactions settled this month to print out
				}
			} // end of if there are transaction that were settled this month

	      		if (DB_num_rows($OstdgTrans)>=1){

		      		$YPos -= ($line_height);
				if ($YPos-(2 * $line_height) <= $Bottom_Margin){
					$PageNumber++;
					$pdf->newPage();
					include ('includes/PDFStatementPageHeader.inc');
				}
			/*Now the same again for outstanding transactions */

			$FontSize=12;
			$pdf->addText($Left_Margin+1,$YPos,$FontSize, _('Outstanding Transactions') );
			$YPos -= $line_height;

			while ($myrow=DB_fetch_array($OstdgTrans)){

				$DisplayAlloc = number_format($myrow['alloc'],2);
				$DisplayOutstanding = number_format($myrow['ostdg'],2);

				$FontSize=9;
				$LeftOvers = $pdf->addTextWrap($Left_Margin+1,$YPos,60,$FontSize,$myrow['typename'], 'left');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+61,$YPos,50,$FontSize,$myrow['transno'], 'left');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+111,$YPos,50,$FontSize,ConvertSQLDate($myrow['trandate']), 'left');

				$FontSize=10;
				if ($myrow['total']>0){
					$DisplayTotal = number_format($myrow['total'],2);
					$LeftOvers = $pdf->addTextWrap($Left_Margin+161,$YPos,55,$FontSize,$DisplayTotal, 'right');
				} else {
					$DisplayTotal = number_format(-$myrow['total'],2);
					$LeftOvers = $pdf->addTextWrap($Left_Margin+216,$YPos,55,$FontSize,$DisplayTotal, 'right');
				}

				$LeftOvers = $pdf->addTextWrap($Left_Margin+271,$YPos,59,$FontSize,$DisplayAlloc, 'right');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+330,$YPos,60,$FontSize,$DisplayOutstanding, 'right');

				/*Now show also in the remittance advice sectin */
				$FontSize=8;
				$LeftOvers = $pdf->addTextWrap($Perforation+1,$YPos,30,$FontSize,$myrow['typename'], 'left');
				$LeftOvers = $pdf->addTextWrap($Perforation+35,$YPos,30,$FontSize,$myrow['transno'], 'left');
				$LeftOvers = $pdf->addTextWrap($Perforation+65,$YPos,60,$FontSize,$DisplayOutstanding, 'right');

				if ($YPos-$line_height <= $Bottom_Margin){
		/* head up a new statement page */

					$PageNumber++;
					$pdf->newPage();
					include ('includes/PDFStatementPageHeader.inc');
				} //end if need a new page headed up

				/*increment a line down for the next line item */
				$YPos -= ($line_height);

			} //end while there are outstanding transaction to print
		} // end if there are outstanding transaction to print


		/* check to see enough space left to print the totals/footer
		which is made up of 2 ruled lines, the totals/aging another 2 lines
		and details of the last payment made - in all 6 lines */
		if (($YPos-$Bottom_Margin)<(4*$line_height)){

		/* head up a new statement/credit note page */
			$PageNumber++;
			$pdf->newPage();
		include ('includes/PDFStatementPageHeader.inc');
		}
			/*Now figure out the aged analysis for the customer under review */

		$SQL = "SELECT debtorsmaster.name,
				currencies.currency,
				paymentterms.terms,
				debtorsmaster.creditlimit,
				holdreasons.dissallowinvoices,
				holdreasons.reasondescription,
				SUM(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight +
				debtortrans.ovdiscount - debtortrans.alloc) AS balance,
				SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
					CASE WHEN (TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate)) >=
					paymentterms.daysbeforedue
					THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight +
					debtortrans.ovdiscount - debtortrans.alloc
					ELSE 0 END
				ELSE 
					CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))','DAY') . ")) >= 0
					THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight +
					debtortrans.ovdiscount - debtortrans.alloc
					ELSE 0 END
				END) AS due,
				Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
					CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
					AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >=
					(paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ")
					THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight +
					debtortrans.ovdiscount - debtortrans.alloc
					ELSE 0 END
				ELSE
					CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1','MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))','DAY') .")) >= " . $_SESSION['PastDueDays1'] . ")
					THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight +
					debtortrans.ovdiscount - debtortrans.alloc
					ELSE 0 END
				END) AS overdue1,
				Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
					CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
					AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue +
					" . $_SESSION['PastDueDays2'] . ")
					THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight +
					debtortrans.ovdiscount - debtortrans.alloc
					ELSE 0 END
				ELSE 
					CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1','MONTH') . "), " . 
					INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))','DAY') . "))
					>= " . $_SESSION['PastDueDays2'] . ")
					THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight +
					debtortrans.ovdiscount - debtortrans.alloc
					ELSE 0 END
				END) AS overdue2
			FROM debtorsmaster INNER JOIN paymentterms
				ON debtorsmaster.paymentterms = paymentterms.termsindicator
			INNER JOIN currencies
				ON debtorsmaster.currcode = currencies.currabrev
			INNER JOIN holdreasons
				ON debtorsmaster.holdreason = holdreasons.reasoncode
			INNER JOIN debtortrans
				ON debtorsmaster.debtorno = debtortrans.debtorno
			WHERE
				debtorsmaster.debtorno = '" . $StmtHeader['debtorno'] . "'
			GROUP BY
				debtorsmaster.name,
				currencies.currency,
				paymentterms.terms,
				paymentterms.daysbeforedue,
				paymentterms.dayinfollowingmonth,
				debtorsmaster.creditlimit,
				holdreasons.dissallowinvoices,
				holdreasons.reasondescription";

			$ErrMsg = 'The customer details could not be retrieved by the SQL because';
			$CustomerResult = DB_query($SQL,$db);

		/*there should be only one record returned ?? */
			$AgedAnalysis = DB_fetch_array($CustomerResult,$db);


		/*Now print out the footer and totals */

			$DisplayDue = number_format($AgedAnalysis['due']-$AgedAnalysis['overdue1'],2);
			$DisplayCurrent = number_format($AgedAnalysis['balance']-$AgedAnalysis['due'],2);
			$DisplayBalance = number_format($AgedAnalysis['balance'],2);
			$DisplayOverdue1 = number_format($AgedAnalysis['overdue1']-$AgedAnalysis['overdue2'],2);
			$DisplayOverdue2 = number_format($AgedAnalysis['overdue2'],2);


			$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+(4*$line_height),$Left_Margin,$Bottom_Margin+(4*$line_height));

			$FontSize=10;


			$pdf->addText($Left_Margin+37, $Bottom_Margin+(3*$line_height)+4, $FontSize, _('Current'). ' ');
			$pdf->addText($Left_Margin+110, $Bottom_Margin+(3*$line_height)+4, $FontSize, _('Past Due').' ');
			$pdf->addText($Left_Margin+182, $Bottom_Margin+(3*$line_height)+4, $FontSize, $_SESSION['PastDueDays1'] . '-' . $_SESSION['PastDueDays2'] . ' ' . _('days') );
			$pdf->addText($Left_Margin+265, $Bottom_Margin+(3*$line_height)+4, $FontSize, _('Over').' ' . $_SESSION['PastDueDays2'] . ' '. _('days'));
			$pdf->addText($Left_Margin+332, $Bottom_Margin+(3*$line_height)+4, $FontSize, _('Total Balance') );

			$LeftOvers = $pdf->addTextWrap($Left_Margin+1, $Bottom_Margin+(2*$line_height)+8,70,$FontSize,$DisplayCurrent, 'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+81, $Bottom_Margin+(2*$line_height)+8,70,$FontSize,$DisplayDue, 'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+161, $Bottom_Margin+(2*$line_height)+8,70,$FontSize,$DisplayOverdue1, 'right');

			$LeftOvers = $pdf->addTextWrap($Left_Margin+251, $Bottom_Margin+(2*$line_height)+8,70,$FontSize,$DisplayOverdue2, 'right');

			$LeftOvers = $pdf->addTextWrap($Left_Margin+324, $Bottom_Margin+(2*$line_height)+8,70,$FontSize,$DisplayBalance, 'right');


			/*draw a line under the balance info */
			$YPos = $Bottom_Margin+(2*$line_height);
			$pdf->line($Left_Margin, $YPos,$Perforation,$YPos);


			if (strlen($StmtHeader['lastpaiddate'])>1 && $StmtHeader['lastpaid']!=0){
				$pdf->addText($Left_Margin+5, $Bottom_Margin+13, $FontSize, _('Last payment received').' ' . ConvertSQLDate($StmtHeader['lastpaiddate']) . ' ' . _('Amount received was').' ' . number_format($StmtHeader['lastpaid'],2));

			}
			/*also show the total due in the remittance section */
			if ($AgedAnalysis['balance']>0){ /*No point showing a negative balance for payment! */
					$FontSize=8;
					$LeftOvers = $pdf->addTextWrap($Perforation+2, $Bottom_Margin+(2*$line_height)+8,40,$FontSize, _('Payment'), 'left');
					$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-90, $Bottom_Margin+(2*$line_height)+8,88,$FontSize,$DisplayBalance, 'right');

			}

		} /* end of check to see that there were statement transactons to print */

	} /* end loop to print statements */

	if (isset($pdf)){


	$pdfcode = $pdf->output();
	$len = strlen($pdfcode);
	header('Content-type: application/pdf');
	header('Content-Length: ' . $len);
	header('Content-Disposition: inline; filename=Statements.pdf');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');

	$pdf->Stream();

	} else {
		$title = _('Print Statments') . ' - ' . _('No Statements Found');
		include('includes/header.inc');
		echo '<BR><BR><BR>' . prnMsg( _('There were no statments to print') );
	        echo '<BR><BR><BR>';
	        include('includes/footer.inc');
	}

} else { /*The option to print PDF was not hit */

	$title = _('Select Statements to Print');
	include('includes/header.inc');
        echo '<BR><BR><BR>';
	if (!isset($_POST['FromCust']) || $_POST['FromCust']=='') {

	/*if FromTransNo is not set then show a form to allow input of either a single statement number or a range of statements to be printed. Also get the last statement number created to show the user where the current range is up to */

		echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '" METHOD="POST"><CENTER><TABLE>';

		echo '<TR><TD>' . _('Starting Customer statement to print (Customer code)'). '
			</TD><TD><input Type=text max=6 size=7 name=FromCust value="1"></TD></TR>
			<TR><TD>'. _('Ending Customer statement to print (Customer code)').'</TD><TD>
				<input Type=text max=6 size=7 name=ToCust value="zzzzzz"></TD></TR></TABLE>
				<BR>
				<INPUT TYPE=Submit Name="PrintPDF" Value="' .
				_('Print All Statements in the Range Selected').'">
			</CENTER>';
	}
	echo '<BR><BR><BR>';
	include('includes/footer.inc');

} /*end of else not PrintPDF */

?>
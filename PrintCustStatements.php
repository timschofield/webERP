<?php
/* $Revision: 1.4 $ */
$PageSecurity = 2;

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/DateFunctions.inc');
//include('includes/class.pdf.php');


If (isset($_POST['PrintPDF']) && isset($_POST['FromCust']) && $_POST['FromCust']!=''){

	$_POST['FromCust'] = strtoupper($_POST['FromCust']);

	If (!isset($_POST['ToCust'])){
	      $_POST['ToCust'] = $_POST['FromCust'];
	} else {
		$_POST['ToCust'] = strtoupper($_POST['ToCust']);
	}

	include('includes/PDFStarter_ros.inc');

	$pdf->addinfo('Title', _('Customer Statements') );
	$pdf->addinfo('Subject', _('Statements from') . ' ' . $_POST['FromCust'] . ' ' . _('to').' ' . $_POST['ToCust']);
	$PageNumber = 1;


	$line_height=16;

	$FirstStatement = True;

	/*We have a range of statements to print so get an array of all the company information */
	$CompanyRecord = ReadInCompanyRecord ($db);
	if ($CompanyRecord==0){
	/*CompanyRecord will be 0 if the company information could not be retrieved */
		echo '<br><br>';
		prnMsg( _('The Company Record could not be loaded') . '. ' . _('This is a major problem') , 'error' );
		include ('includes/footer.inc');
	     	exit;
	}

/* Do a quick tidy up to settle any transactions that should have been settled at the time of allocation but for whatever reason weren't */
	$ErrMsg = _('There was a problem settling the old transactions.');
	$DbgMsg = _('The SQL used to settle outstanding transactions was');
	$sql = "UPDATE DebtorTrans SET Settled=1
		WHERE ABS(DebtorTrans.OvAmount+DebtorTrans.OvDiscount+DebtorTrans.OvFreight+DebtorTrans.OvGST-DebtorTrans.Alloc)<0.009";
	$SettleAsNec = DB_query($sql,$db, $ErrMsg, $DbgMsg);

/*Figure out who all the customers in this range are */
	$ErrMsg= _('There was a problem retrieving the customer information for the statements from the database');
	$sql = "SELECT DebtorNo,
			Name,
			Address1,
			Address2,
			Address3,
			Address4,
			LastPaid,
			LastPaidDate,
			Currency,
			Terms
		FROM DebtorsMaster INNER JOIN Currencies
			ON DebtorsMaster.CurrCode=Currencies.CurrAbrev
		INNER JOIN PaymentTerms
			ON DebtorsMaster.PaymentTerms=PaymentTerms.TermsIndicator
		WHERE DebtorNo >='" . $_POST['FromCust'] ."'
		AND DebtorNo <='" . $_POST['ToCust'] ."'
		ORDER BY DebtorNo";
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
		$ErrMsg =  _('There was a problem retrieving the outstanding transactions for') . ' ' .	$StmtHeader['Name'] . ' '. _('from the database') . '.';
		$sql = "SELECT SysTypes.TypeName,
				DebtorTrans.TransNo,
				DebtorTrans.TranDate,
				DebtorTrans.OvAmount+DebtorTrans.OvDiscount+DebtorTrans.OvFreight+DebtorTrans.OvGST AS Total,
				DebtorTrans.Alloc,
				DebtorTrans.OvAmount+DebtorTrans.OvDiscount+DebtorTrans.OvFreight+DebtorTrans.OvGST-DebtorTrans.Alloc AS Ostdg
			FROM DebtorTrans INNER JOIN SysTypes
				ON DebtorTrans.Type=SysTypes.TypeID
			WHERE DebtorTrans.DebtorNo='" . $StmtHeader["DebtorNo"] . "'
			AND DebtorTrans.Settled=0
			ORDER BY DebtorTrans.ID";

		$OstdgTrans=DB_query($sql,$db, $ErrMsg);

	   	$NumberOfRecordsReturned = DB_num_rows($OstdgTrans);

/*now get all the settled transactions which were allocated this month */
		$ErrMsg = _('There was a problem retrieving the transactions that were settled over the course of the last month for'). ' ' . $StmtHeader["Name"] . ' ' . _('from the database');
	   	if ($Show_Settled_LastMonth==1){
	   		$sql = "SELECT DISTINCT SysTypes.TypeName,
						DebtorTrans.TransNo,
						DebtorTrans.TranDate,
						DebtorTrans.OvAmount+DebtorTrans.OvDiscount+DebtorTrans.OvFreight+DebtorTrans.OvGST AS Total,
						DebtorTrans.Alloc,
						DebtorTrans.OvAmount+DebtorTrans.OvDiscount+DebtorTrans.OvFreight+DebtorTrans.OvGST-DebtorTrans.Alloc AS Ostdg
				FROM DebtorTrans INNER JOIN SysTypes
					ON DebtorTrans.Type=SysTypes.TypeID
				INNER JOIN CustAllocns
					ON (DebtorTrans.ID=CustAllocns.TransID_AllocFrom
						OR DebtorTrans.ID=CustAllocns.TransID_AllocTo)
				WHERE CustAllocns.DateAlloc >='" .
					Date("Y-m-d",Mktime(0,0,0,Date("m")-1,Date("d"),Date("y"))) . "'
				AND DebtorTrans.DebtorNo='" . $StmtHeader["DebtorNo"] . "' AND DebtorTrans.Settled=1
				ORDER BY DebtorTrans.ID";

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


			if ($Show_Settled_LastMonth==1){
				if (DB_num_rows($SetldTrans)>=1) {

					$FontSize=12;
					$YPos -= $line_height;
					$pdf->addText($Left_Margin+1,$YPos,$FontSize, _('Settled Transactions'));

					$YPos -= ($line_height);

					$FontSize=10;

					while ($myrow=DB_fetch_array($SetldTrans)){

						$DisplayAlloc = number_format($myrow['Alloc'],2);
						$DisplayOutstanding = number_format($myrow['Ostdg'],2);

						$FontSize=9;

						$LeftOvers = $pdf->addTextWrap($Left_Margin+1,$YPos,60,$FontSize,$myrow['TypeName'], 'left');
						$LeftOvers = $pdf->addTextWrap($Left_Margin+61,$YPos,50,$FontSize,$myrow['TransNo'], 'left');
						$LeftOvers = $pdf->addTextWrap($Left_Margin+111,$YPos,50,$FontSize,ConvertSQLDate($myrow['TranDate']), 'left');

						$FontSize=10;
						if ($myrow['Total']>0){
							$DisplayTotal = number_format($myrow['Total'],2);
							$LeftOvers = $pdf->addTextWrap($Left_Margin+150,$YPos,60,$FontSize,$DisplayTotal, 'right');
						} else {
							$DisplayTotal = number_format(-$myrow['Total'],2);
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

				$DisplayAlloc = number_format($myrow['Alloc'],2);
				$DisplayOutstanding = number_format($myrow['Ostdg'],2);

				$FontSize=9;
				$LeftOvers = $pdf->addTextWrap($Left_Margin+1,$YPos,60,$FontSize,$myrow['TypeName'], 'left');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+61,$YPos,50,$FontSize,$myrow['TransNo'], 'left');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+111,$YPos,50,$FontSize,ConvertSQLDate($myrow['TranDate']), 'left');

				$FontSize=10;
				if ($myrow['Total']>0){
					$DisplayTotal = number_format($myrow['Total'],2);
					$LeftOvers = $pdf->addTextWrap($Left_Margin+161,$YPos,55,$FontSize,$DisplayTotal, 'right');
				} else {
					$DisplayTotal = number_format(-$myrow['Total'],2);
					$LeftOvers = $pdf->addTextWrap($Left_Margin+216,$YPos,55,$FontSize,$DisplayTotal, 'right');
				}

				$LeftOvers = $pdf->addTextWrap($Left_Margin+271,$YPos,59,$FontSize,$DisplayAlloc, 'right');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+330,$YPos,60,$FontSize,$DisplayOutstanding, 'right');

				/*Now show also in the remittance advice sectin */
				$FontSize=8;
				$LeftOvers = $pdf->addTextWrap($Perforation+1,$YPos,30,$FontSize,$myrow['TypeName'], 'left');
				$LeftOvers = $pdf->addTextWrap($Perforation+35,$YPos,30,$FontSize,$myrow['TransNo'], 'left');
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

		$SQL = "SELECT DebtorsMaster.Name,
				Currencies.Currency,
				PaymentTerms.Terms,
				DebtorsMaster.CreditLimit,
				HoldReasons.DissallowInvoices,
				HoldReasons.ReasonDescription,
				Sum(DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight +
				DebtorTrans.OvDiscount - DebtorTrans.Alloc) AS Balance,
				Sum(IF (PaymentTerms.DaysBeforeDue > 0,
				CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DebtorTrans.TranDate)) >=
					PaymentTerms.DaysBeforeDue
				THEN DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight +
					DebtorTrans.OvDiscount - DebtorTrans.Alloc
				ELSE 0 END,
				CASE WHEN
				TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(DebtorTrans.TranDate, INTERVAL 1 MONTH),
				INTERVAL (PaymentTerms.DayInFollowingMonth - DAYOFMONTH(DebtorTrans.TranDate)) DAY)) >= 0
				THEN DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight +
					DebtorTrans.OvDiscount - DebtorTrans.Alloc
				ELSE 0 END
				)) AS Due,
				Sum(IF (
				PaymentTerms.DaysBeforeDue > 0,
				CASE WHEN TO_DAYS(Now()) - TO_DAYS(DebtorTrans.TranDate) > PaymentTerms.DaysBeforeDue
					AND TO_DAYS(Now()) - TO_DAYS(DebtorTrans.TranDate) >=
					(PaymentTerms.DaysBeforeDue + " . $PastDueDays1 . ")
				THEN DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight +
					DebtorTrans.OvDiscount - DebtorTrans.Alloc
				ELSE 0 END,
				CASE WHEN
				(TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(DebtorTrans.TranDate, INTERVAL 1  MONTH),
				INTERVAL (PaymentTerms.DayInFollowingMonth - DAYOFMONTH(DebtorTrans.TranDate)) DAY))
				>= " . $PastDueDays1 . ")
				THEN DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight +
					DebtorTrans.OvDiscount - DebtorTrans.Alloc
				ELSE 0 END
				)) AS Overdue1,
				Sum(IF (
					PaymentTerms.DaysBeforeDue > 0,
				CASE WHEN TO_DAYS(Now()) - TO_DAYS(DebtorTrans.TranDate) > PaymentTerms.DaysBeforeDue
					AND TO_DAYS(Now()) - TO_DAYS(DebtorTrans.TranDate) >= (PaymentTerms.DaysBeforeDue +
					" . $PastDueDays2 . ")
				THEN DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight +
					DebtorTrans.OvDiscount - DebtorTrans.Alloc
				ELSE 0 END,
				CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(DebtorTrans.TranDate, INTERVAL 1 MONTH),
					INTERVAL (PaymentTerms.DayInFollowingMonth - DAYOFMONTH(DebtorTrans.TranDate)) DAY))
					>= " . $PastDueDays2 . ")
				THEN DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight +
					DebtorTrans.OvDiscount - DebtorTrans.Alloc
				ELSE 0 END
				)) AS Overdue2
			FROM DebtorsMaster INNER JOIN PaymentTerms
				ON DebtorsMaster.PaymentTerms = PaymentTerms.TermsIndicator
			INNER JOIN Currencies
				ON DebtorsMaster.CurrCode = Currencies.CurrAbrev
			INNER JOIN HoldReasons
				ON DebtorsMaster.HoldReason = HoldReasons.ReasonCode
			INNER JOIN DebtorTrans
				ON DebtorsMaster.DebtorNo = DebtorTrans.DebtorNo
			WHERE
				DebtorsMaster.DebtorNo = '" . $StmtHeader["DebtorNo"] . "'
			GROUP BY
				DebtorsMaster.Name,
				Currencies.Currency,
				PaymentTerms.Terms,
				PaymentTerms.DaysBeforeDue,
				PaymentTerms.DayInFollowingMonth,
				DebtorsMaster.CreditLimit,
				HoldReasons.DissallowInvoices,
				HoldReasons.ReasonDescription";

			$ErrMsg = 'The customer details could not be retrieved by the SQL because';
			$CustomerResult = DB_query($SQL,$db);

		/*there should be only one record returned ?? */
			$AgedAnalysis = DB_fetch_array($CustomerResult,$db);


		/*Now print out the footer and totals */

			$DisplayDue = number_format($AgedAnalysis['Due']-$AgedAnalysis['Overdue1'],2);
			$DisplayCurrent = number_format($AgedAnalysis['Balance']-$AgedAnalysis['Due'],2);
			$DisplayBalance = number_format($AgedAnalysis['Balance'],2);
			$DisplayOverdue1 = number_format($AgedAnalysis['Overdue1']-$AgedAnalysis['Overdue2'],2);
			$DisplayOverdue2 = number_format($AgedAnalysis['Overdue2'],2);


			$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+(4*$line_height),$Left_Margin,$Bottom_Margin+(4*$line_height));

			$FontSize=10;


			$pdf->addText($Left_Margin+37, $Bottom_Margin+(3*$line_height)+4, $FontSize, _('Current'). ' ');
			$pdf->addText($Left_Margin+110, $Bottom_Margin+(3*$line_height)+4, $FontSize, _('Past Due').' ');
			$pdf->addText($Left_Margin+182, $Bottom_Margin+(3*$line_height)+4, $FontSize, $PastDueDays1 . '-' . $PastDueDays2 . ' ' . _('days') );
			$pdf->addText($Left_Margin+265, $Bottom_Margin+(3*$line_height)+4, $FontSize, _('Over').' ' . $PastDueDays2 . ' '. _('days'));
			$pdf->addText($Left_Margin+332, $Bottom_Margin+(3*$line_height)+4, $FontSize, _('Total Balance') );

			$LeftOvers = $pdf->addTextWrap($Left_Margin+1, $Bottom_Margin+(2*$line_height)+8,70,$FontSize,$DisplayCurrent, 'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+81, $Bottom_Margin+(2*$line_height)+8,70,$FontSize,$DisplayDue, 'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+161, $Bottom_Margin+(2*$line_height)+8,70,$FontSize,$DisplayOverdue1, 'right');

			$LeftOvers = $pdf->addTextWrap($Left_Margin+251, $Bottom_Margin+(2*$line_height)+8,70,$FontSize,$DisplayOverdue2, 'right');

			$LeftOvers = $pdf->addTextWrap($Left_Margin+324, $Bottom_Margin+(2*$line_height)+8,70,$FontSize,$DisplayBalance, 'right');


			/*draw a line under the balance info */
			$YPos = $Bottom_Margin+(2*$line_height);
			$pdf->line($Left_Margin, $YPos,$Perforation,$YPos);


			if (strlen($StmtHeader['LastPaidDate'])>1 && $StmtHeader['LastPaid']!=0){
				$pdf->addText($Left_Margin+5, $Bottom_Margin+13, $FontSize, _('Last payment received').' ' . ConvertSQLDate($StmtHeader['LastPaidDate']) . ' ' . _('Amount received was').' ' . number_format($StmtHeader['LastPaid'],2));

			}
			/*also show the total due in the remittance section */
			if ($AgedAnalysis['Balance']>0){ /*No point showing a negative balance for payment! */
					$FontSize=8;
					$LeftOvers = $pdf->addTextWrap($Perforation+2, $Bottom_Margin+(3*$line_height)+8,40,$FontSize, _('Payment'), 'left');
					$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-90, $Bottom_Margin+(3*$line_height)+8,88,$FontSize,$DisplayBalance, 'right');

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

<?php
if (!isset($_POST['FromCust'])  OR $_POST['FromCust']=="") {
	$title="Select Statements to Print";
}

$PageSecurity = 2;

include("includes/SQL_CommonFunctions.inc");
include("includes/DateFunctions.inc");

If (isset($_POST['PrintPDF']) && isset($_POST['FromCust']) && $_POST['FromCust']!=""){

	include("config.php");
	include("includes/ConnectDB.inc");



	$_POST['FromCust'] = strtoupper($_POST['FromCust']);

	If (!isset($_POST['ToCust'])){
	      $_POST['ToCust'] = $_POST['FromCust'];
	} else {
		$_POST['ToCust'] = strtoupper($_POST['ToCust']);
	}

	include("includes/PDFStarter_ros.inc");

	$pdf->addinfo('Title',"Customer Statements");
	$pdf->addinfo('Subject',"Statements from " . $_POST["FromCust"] . " to " . $_POST["ToCust"]);
	$PageNumber = 1;


	$line_height=16;

	$FirstStatement = True;

	/*We have a range of statements to print so get an array of all the company information */
	$CompanyRecord = ReadInCompanyRecord ($db);
	if ($CompanyRecord==0){
	/*CompanyRecord will be 0 if the company information could not be retrieved */
	     exit;
	}

/* Do a quick tidy up to settle any transactions that should have been settled at the time of allocation but for whatever reason weren't */
	$sql = "UPDATE DebtorTrans SET Settled=1 WHERE ABS(DebtorTrans.OvAmount+DebtorTrans.OvDiscount+DebtorTrans.OvFreight+DebtorTrans.OvGST-DebtorTrans.Alloc)<0.009";
	$SettleAsNec = DB_query($sql,$db);
	if (DB_error_no($db)!=0) {
	    echo "<BR>There was a problem settling the old transactions.";
	    if ($debug==1){
		 echo "<BR>The SQL used to settle outstanding transactions was:<BR>$sql";
	    }
	    break;
	    exit;
	}

/*Figure out who all the customers in this range are */
	$sql = "SELECT DebtorNo, Name, Address1, Address2, Address3, Address4, LastPaid, LastPaidDate, Currency, Terms FROM DebtorsMaster, Currencies, PaymentTerms WHERE DebtorsMaster.CurrCode=Currencies.CurrAbrev AND DebtorsMaster.PaymentTerms=PaymentTerms.TermsIndicator AND DebtorNo >='" . $_POST['FromCust'] ."' AND DebtorNo <='" . $_POST['ToCust'] ."' ORDER BY DebtorNo";
	$StatementResults=DB_query($sql,$db);
	if (DB_error_no($db)!=0) {
	    echo "There was a problem retrieving the customer information for the statements from the database.";
	    if ($debug==1){
		 echo "<BR>The SQL used to get this information (that failed) was:<BR>$sql";
	    }
	    break;
	    exit;
	}

	while ($StmtHeader=DB_fetch_array($StatementResults)){	 /*loop through all the customers returned */

	/*now get all the outstanding transaction ie Settled=0 */
		$sql = "SELECT SysTypes.TypeName, DebtorTrans.TransNo, DebtorTrans.TranDate, DebtorTrans.OvAmount+DebtorTrans.OvDiscount+DebtorTrans.OvFreight+DebtorTrans.OvGST AS Total, DebtorTrans.Alloc, DebtorTrans.OvAmount+DebtorTrans.OvDiscount+DebtorTrans.OvFreight+DebtorTrans.OvGST-DebtorTrans.Alloc As Ostdg FROM DebtorTrans, SysTypes WHERE DebtorTrans.Type=SysTypes.TypeID AND DebtorTrans.DebtorNo='" . $StmtHeader["DebtorNo"] . "' AND DebtorTrans.Settled=0 ORDER BY DebtorTrans.ID";

		$OstdgTrans=DB_query($sql,$db);

		if (DB_error_no($db)!=0) {
			echo "There was a problem retrieving the outstanding transactions for " . $StmtHeader["Name"] . " from the database.";
			if ($debug==1){
		    		echo "<BR>The SQL used to get this information (that failed) was:<BR>$sql";
			}
			break;
			exit;
	   	}

	   	$NumberOfRecordsReturned = DB_num_rows($OstdgTrans);


/*now get all the settled transactions which were allocated this month */
	   	if ($Show_Settled_LastMonth==1){
	   		$sql = "SELECT DISTINCT SysTypes.TypeName, DebtorTrans.TransNo, DebtorTrans.TranDate, DebtorTrans.OvAmount+DebtorTrans.OvDiscount+DebtorTrans.OvFreight+DebtorTrans.OvGST AS Total, DebtorTrans.Alloc, DebtorTrans.OvAmount+DebtorTrans.OvDiscount+DebtorTrans.OvFreight+DebtorTrans.OvGST-DebtorTrans.Alloc As Ostdg FROM DebtorTrans, CustAllocns, SysTypes WHERE DebtorTrans.Type=SysTypes.TypeID AND (DebtorTrans.ID=CustAllocns.TransID_AllocFrom OR DebtorTrans.ID=CustAllocns.TransID_AllocTo) AND CustAllocns.DateAlloc >='" . Date("Y/m/d",Mktime(0,0,0,Date("m")-1,Date("d"),Date("y"))) . "' AND DebtorTrans.DebtorNo='" . $StmtHeader["DebtorNo"] . "' AND DebtorTrans.Settled=1 ORDER BY DebtorTrans.ID";

			$SetldTrans=DB_query($sql,$db);
	   		if (DB_error_no($db)!=0) {
				echo "<BR>There was a problem retrieving the transactions that were settled over the course of the last month for " . $StmtHeader["Name"] . " from the database.";
				if ($debug==1){
		    			echo "The SQL used to get this information (that failed) was:<BR>$sql";
				}
				break;
				exit;
	   		} else {
				$NumberOfRecordsReturned += DB_num_rows($SetldTrans);
			}
	   	}

	  	if ( $NumberOfRecordsReturned >=1){

		/* Then there's a statement to print. So print out the statement header from the company record */

	      		$PageNumber =1;

			if ($FirstStatement==True){
				$FirstStatement=False;
	      		} else {
				$pdf->newPage();
	      		}

	      		include("includes/PDFStatementPageHeader.inc");


			if ($Show_Settled_LastMonth==1){
				if (DB_num_rows($SetldTrans)>=1) {

					$FontSize=12;
					$YPos -= $line_height;
					$pdf->addText($Left_Margin+1,$YPos,$FontSize, "Settled Transactions");

					$YPos -= ($line_height);

					$FontSize=10;

					while ($myrow=DB_fetch_array($SetldTrans)){

						$DisplayAlloc = number_format($myrow["Alloc"],2);
						$DisplayOutstanding = number_format($myrow["Ostdg"],2);

						$FontSize=9;

						$LeftOvers = $pdf->addTextWrap($Left_Margin+1,$YPos,60,$FontSize,$myrow["TypeName"], 'left');
						$LeftOvers = $pdf->addTextWrap($Left_Margin+61,$YPos,50,$FontSize,$myrow["TransNo"], 'left');
						$LeftOvers = $pdf->addTextWrap($Left_Margin+111,$YPos,50,$FontSize,ConvertSQLDate($myrow["TranDate"]), 'left');

						$FontSize=10;
						if ($myrow["Total"]>0){
							$DisplayTotal = number_format($myrow["Total"],2);
							$LeftOvers = $pdf->addTextWrap($Left_Margin+150,$YPos,60,$FontSize,$DisplayTotal, 'right');
						} else {
							$DisplayTotal = number_format(-$myrow["Total"],2);
							$LeftOvers = $pdf->addTextWrap($Left_Margin+210,$YPos,60,$FontSize,$DisplayTotal, 'right');
						}
						$LeftOvers = $pdf->addTextWrap($Left_Margin+270,$YPos,60,$FontSize,$DisplayAlloc, 'right');
						$LeftOvers = $pdf->addTextWrap($Left_Margin+330,$YPos,60,$FontSize,$DisplayOutstanding, 'right');

						if ($YPos-$line_height <= $Bottom_Margin){
		/* head up a new statement page */

							$PageNumber++;
							$pdf->newPage();
							include ("includes/PDFStatementPageHeader.inc");
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
					include ("includes/PDFStatementPageHeader.inc");
				}
			/*Now the same again for outstanding transactions */

			$FontSize=12;
			$pdf->addText($Left_Margin+1,$YPos,$FontSize, "Outstanding Transactions");
			$YPos -= $line_height;

			while ($myrow=DB_fetch_array($OstdgTrans)){

				$DisplayAlloc = number_format($myrow["Alloc"],2);
				$DisplayOutstanding = number_format($myrow["Ostdg"],2);

				$FontSize=9;
				$LeftOvers = $pdf->addTextWrap($Left_Margin+1,$YPos,60,$FontSize,$myrow["TypeName"], 'left');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+61,$YPos,50,$FontSize,$myrow["TransNo"], 'left');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+111,$YPos,50,$FontSize,ConvertSQLDate($myrow["TranDate"]), 'left');

				$FontSize=10;
				if ($myrow["Total"]>0){
					$DisplayTotal = number_format($myrow["Total"],2);
					$LeftOvers = $pdf->addTextWrap($Left_Margin+161,$YPos,55,$FontSize,$DisplayTotal, 'right');
				} else {
					$DisplayTotal = number_format(-$myrow["Total"],2);
					$LeftOvers = $pdf->addTextWrap($Left_Margin+216,$YPos,55,$FontSize,$DisplayTotal, 'right');
				}

				$LeftOvers = $pdf->addTextWrap($Left_Margin+271,$YPos,59,$FontSize,$DisplayAlloc, 'right');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+330,$YPos,60,$FontSize,$DisplayOutstanding, 'right');

				/*Now show also in the remittance advice sectin */
				$FontSize=8;
				$LeftOvers = $pdf->addTextWrap($Perforation+1,$YPos,30,$FontSize,$myrow["TypeName"], 'left');
				$LeftOvers = $pdf->addTextWrap($Perforation+35,$YPos,30,$FontSize,$myrow["TransNo"], 'left');
				$LeftOvers = $pdf->addTextWrap($Perforation+65,$YPos,60,$FontSize,$DisplayOutstanding, 'right');

				if ($YPos-$line_height <= $Bottom_Margin){
		/* head up a new statement page */

				$PageNumber++;
				$pdf->newPage();
				include ("includes/PDFStatementPageHeader.inc");
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
			include ("includes/PDFStatementPageHeader.inc");
			}
			/*Now figure out the aged analysis for the customer under review */

			$SQL = "SELECT DebtorsMaster.Name, Currencies.Currency, PaymentTerms.Terms,
	DebtorsMaster.CreditLimit, HoldReasons.DissallowInvoices, HoldReasons.ReasonDescription,

	Sum(DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight + DebtorTrans.OvDiscount - DebtorTrans.Alloc) AS Balance,

	Sum(IF (PaymentTerms.DaysBeforeDue > 0,
		CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DebtorTrans.TranDate)) >= PaymentTerms.DaysBeforeDue  THEN DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight + DebtorTrans.OvDiscount - DebtorTrans.Alloc ELSE 0 END,

		CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(DebtorTrans.TranDate, INTERVAL 1 MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth - DAYOFMONTH(DebtorTrans.TranDate)) DAY)) >= 0 THEN DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight + DebtorTrans.OvDiscount - DebtorTrans.Alloc ELSE 0 END

	)) AS Due,


	Sum(IF (PaymentTerms.DaysBeforeDue > 0,
		CASE WHEN TO_DAYS(Now()) - TO_DAYS(DebtorTrans.TranDate) > PaymentTerms.DaysBeforeDue	AND TO_DAYS(Now()) - TO_DAYS(DebtorTrans.TranDate) >= (PaymentTerms.DaysBeforeDue + " . $PastDueDays1 . ") THEN DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight + DebtorTrans.OvDiscount - DebtorTrans.Alloc ELSE 0 END,

		CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(DebtorTrans.TranDate, INTERVAL 1  MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth - DAYOFMONTH(DebtorTrans.TranDate)) DAY)) >= " . $PastDueDays1 . ") THEN DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight + DebtorTrans.OvDiscount - DebtorTrans.Alloc ELSE 0 END

	)) AS Overdue1,

	Sum(IF (PaymentTerms.DaysBeforeDue > 0,
		CASE WHEN TO_DAYS(Now()) - TO_DAYS(DebtorTrans.TranDate) > PaymentTerms.DaysBeforeDue	AND TO_DAYS(Now()) - TO_DAYS(DebtorTrans.TranDate) >= (PaymentTerms.DaysBeforeDue + " . $PastDueDays2 . ") THEN DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight + DebtorTrans.OvDiscount - DebtorTrans.Alloc ELSE 0 END,

		CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(DebtorTrans.TranDate, INTERVAL 1  MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth - DAYOFMONTH(DebtorTrans.TranDate)) DAY)) >= " . $PastDueDays2 . ") THEN DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight + DebtorTrans.OvDiscount - DebtorTrans.Alloc ELSE 0 END

	)) AS Overdue2

	FROM DebtorsMaster, PaymentTerms, HoldReasons, Currencies, DebtorTrans WHERE DebtorsMaster.PaymentTerms = PaymentTerms.TermsIndicator AND DebtorsMaster.CurrCode = Currencies.CurrAbrev AND DebtorsMaster.HoldReason = HoldReasons.ReasonCode AND DebtorsMaster.DebtorNo = '" . $StmtHeader["DebtorNo"] . "' AND DebtorsMaster.DebtorNo = DebtorTrans.DebtorNo GROUP BY DebtorsMaster.Name, Currencies.Currency, PaymentTerms.Terms, PaymentTerms.DaysBeforeDue, PaymentTerms.DayInFollowingMonth, DebtorsMaster.CreditLimit, HoldReasons.DissallowInvoices, HoldReasons.ReasonDescription";

			$CustomerResult = DB_query($SQL,$db);

			if (DB_error_no($db) !=0) {
			echo "The customer details could not be retrieved by the SQL because - " . DB_error_msg($db);

			if ($debug==1){
			echo "<BR>The SQL statement that caused the trouble was:<BR>$SQL";
			}
			exit;
			}
			/*there should be only one record returned ?? */
			$AgedAnalysis = DB_fetch_array($CustomerResult,$db);


		/*Now print out the footer and totals */

			$DisplayDue = number_format($AgedAnalysis["Due"]-$AgedAnalysis["Overdue1"],2);
			$DisplayCurrent = number_format($AgedAnalysis["Balance"]-$AgedAnalysis["Due"],2);
			$DisplayBalance = number_format($AgedAnalysis["Balance"],2);
			$DisplayOverdue1 = number_format($AgedAnalysis["Overdue1"]-$AgedAnalysis["Overdue2"],2);
			$DisplayOverdue2 = number_format($AgedAnalysis["Overdue2"],2);


			$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+(4*$line_height),$Left_Margin,$Bottom_Margin+(4*$line_height));

			$FontSize=10;


			$pdf->addText($Left_Margin+37, $Bottom_Margin+(3*$line_height)+4, $FontSize, "Current ");
			$pdf->addText($Left_Margin+110, $Bottom_Margin+(3*$line_height)+4, $FontSize, "Past Due ");
			$pdf->addText($Left_Margin+182, $Bottom_Margin+(3*$line_height)+4, $FontSize, $PastDueDays1 . "-" . $PastDueDays2 . " days");
			$pdf->addText($Left_Margin+265, $Bottom_Margin+(3*$line_height)+4, $FontSize, "Over " . $PastDueDays2 . "days");
			$pdf->addText($Left_Margin+332, $Bottom_Margin+(3*$line_height)+4, $FontSize, "Total Balance");

			$LeftOvers = $pdf->addTextWrap($Left_Margin+1, $Bottom_Margin+(2*$line_height)+8,70,$FontSize,$DisplayCurrent, 'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+81, $Bottom_Margin+(2*$line_height)+8,70,$FontSize,$DisplayDue, 'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+161, $Bottom_Margin+(2*$line_height)+8,70,$FontSize,$DisplayOverdue1, 'right');

			$LeftOvers = $pdf->addTextWrap($Left_Margin+251, $Bottom_Margin+(2*$line_height)+8,70,$FontSize,$DisplayOverdue2, 'right');

			$LeftOvers = $pdf->addTextWrap($Left_Margin+324, $Bottom_Margin+(2*$line_height)+8,70,$FontSize,$DisplayBalance, 'right');


			/*draw a line under the balance info */
			$YPos = $Bottom_Margin+(2*$line_height);
			$pdf->line($Left_Margin, $YPos,$Perforation,$YPos);


			if (strlen($StmtHeader["LastPaidDate"])>1 && $StmtHeader["LastPaid"]!=0){
				$pdf->addText($Left_Margin+5, $Bottom_Margin+13, $FontSize, "Last payment received " . ConvertSQLDate($StmtHeader["LastPaidDate"]) . ". Amount received " . number_format($StmtHeader["LastPaid"],2));

			}
			/*also show the total due in the remittance section */
			if ($AgedAnalysis["Balance"]>0){ /*No point showing a negative balance for payment! */
					$FontSize=8;
					$LeftOvers = $pdf->addTextWrap($Perforation+2, $Bottom_Margin+(3*$line_height)+8,40,$FontSize,"Payment", 'left');
					$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-90, $Bottom_Margin+(3*$line_height)+8,88,$FontSize,$DisplayBalance, 'right');

			}

		} /* end of check to see that there were statement transactons to print */

	} /* end loop to print statements */

	if (isset($pdf)){


	$pdfcode = $pdf->output();
	$len = strlen($pdfcode);
	header("Content-type: application/pdf");
	header("Content-Length: " . $len);
	header("Content-Disposition: inline; filename=Statements.pdf");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Pragma: public");

	$pdf->Stream();

	} else {
		echo "<BR><BR>There were no statments to print";
	}


} else { /*The option to print PDF was not hit */

	include("includes/session.inc");
	include("includes/header.inc");


	if (!isset($_POST['FromCust']) || $_POST['FromCust']=="") {

	/*if FromTransNo is not set then show a form to allow input of either a single statement number or a range of statements to be printed. Also get the last statement number created to show the user where the current range is up to */

		echo "<FORM ACTION=" . $_SERVER['PHP_SELF'] . " METHOD='POST'><CENTER><TABLE>";

		echo "<TR><TD>Starting Customer statement to print (Customer code)</TD><TD><input Type=text max=6 size=7 name=FromCust value='1'></TD></TR>";
		echo "<TR><TD>Ending Customer statement to print (Customer code)</TD><TD><input Type=text max=6 size=7 name=ToCust value='zzzzzz'></TD></TR></TABLE>";
		echo "<INPUT TYPE=Submit Name='PrintPDF' Value='Print All Statements in the Range Selected'></CENTER>";
	}
	include("includes/footer.inc");

} /*end of else not PrintPDF */

?>

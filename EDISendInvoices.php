<?php
/* $Revision: 1.5 $ */
$PageSecurity =15;

include ('includes/session.inc');
include ('includes/header.inc');
include ('includes/DateFunctions.inc');
include('includes/SQL_CommonFunctions.inc'); //need for EDITransNo
include('includes/htmlMimeMail.php'); // need for sending email attachments

/*Get the Customers who are enabled for EDI invoicing */
$sql = 'SELECT DebtorNo,
		EDIReference,
		EDITransport,
		EDIAddress,
		EDIServerUser,
		EDIServerPwd,
		DaysBeforeDue,
		DayInFollowingMonth
	FROM DebtorsMaster INNER JOIN PaymentTerms ON DebtorsMaster.PaymentTerms=PaymentTerms.TermsIndicator
	WHERE EDIInvoices=1';

$EDIInvCusts = DB_query($sql,$db);

if (DB_num_rows($EDIInvCusts)==0){
	exit;
}

$CompanyRecord = ReadInCompanyRecord($db);

while ($CustDetails = DB_fetch_array($EDIInvCusts)){

	/*Figure out if there are any unset invoices or credits for the customer */

	$sql = "SELECT DebtorTrans.ID,
			TransNo,
			Type,
			Order_,
			TranDate,
			OvGST,
			OvAmount,
			OvFreight,
			OvDiscount,
			DebtorTrans.BranchCode,
			CustBranchCode,
			InvText,
			ShipVia,
			Rate,
			BrName,
			BrAddress1,
			BrAddress2,
			BrAddress3,
			BrAddress4
		FROM DebtorTrans INNER JOIN CustBranch ON CustBranch.DebtorNo = DebtorTrans.DebtorNo
		AND CustBranch.BranchCode = DebtorTrans.BranchCode
		WHERE (Type=10 OR Type=11)
		AND EDISent=0
		AND DebtorTrans.DebtorNo='" . $CustDetails['DebtorNo'] . "'";

	$ErrMsg = _('There was a problem retrieving the customer transactions because');
	$TransHeaders = DB_query($sql,$db,$ErrMsg);


	if (DB_num_rows($TransHeaders)==0){
		break; /*move on to the next EDI customer */
	}

	/*Setup the variable from the DebtorsMaster required for the message */
	$CompanyEDIReference = $EDIReference;
	$CustEDIReference = $CustDetails['EDIReference'];
	$TaxAuthorityRef = $CompanyRecord['GSTNo'];

	while ($TransDetails = DB_fetch_array($TransHeaders)){

/*Set up the variables that will be needed in construction of the EDI message */
		if ($TransDetails['Type']==10){ /* its an invoice */
			$InvOrCrd = 388;
		} else { /* its a credit note */
			$InvOrCrd = 381;
		}
		$TransNo = $TransDetails['TransNo'];
		/*Always an original in this script since only non-sent transactions being processed */
		$OrigOrDup = 9;
		$TranDate = SQLDateToEDI($TransDetails['TranDate']);
		$OrderNo = $TransDetails['Order_'];
		$CustBranchCode = $TransDetails['CustBranchCode'];
		$BranchName = $TransDetails['BrName'];
		$BranchStreet =$TransDetails['BrAddress1'];
		$BranchCity = $TransDetails['BrAddress2'];
		$BranchState = $TransDetails['BrAddress3'];
		$ExchRate = $TransDetails['Rate'];
		$TaxTotal = $TransDetails['OvGST'];

		$DatePaymentDue = ConvertToEDIDate(CalcDueDate(ConvertSQLDate($TransDetails['TranDate']),$CustDetails['DayInFollowingMonth'], $CustDetails['DaysBeforeDue']));

		$TotalAmountExclTax = $TransDetails['OvAmount']+ $TransDetails['OvFreight'] + $TransDetails['OvDiscount'];
		$TotalAmountInclTax = $TransDetails['OvAmount']+ $TransDetails['OvFreight'] + $TransDetails['OvDiscount'] + $TransDetails['OvGST'];

		/* NOW ... Get the message lines
			then replace variable names with data
			write the output to a file one line at a time */

		$sql = "SELECT Section, LineText FROM EDIMessageFormat WHERE PartnerCode='" . $CustDetails['DebtorNo'] . "' AND MessageType='INVOIC' ORDER BY SequenceNo";
		$ErrMsg =  _('An error occurred in getting the EDI format template for') . ' ' . $CustDetails['DebtorNo'] . ' ' . _('because');
		$MessageLinesResult = DB_query($sql, $db,$ErrMsg);


		if (DB_num_rows($MessageLinesResult)>0){


			$DetailLines = array();
			$ArrayCounter =0;
			While ($MessageLine = DB_fetch_array($MessageLinesResult)){
				if ($MessageLine['Section']=='Detail'){
					$DetailLines[$ArrayCounter]=$MessageLine['LineText'];
					$ArrayCounter++;
				}
			}
			DB_data_seek($MessageLinesResult,0);

			$EDITransNo = GetNextTransNo(99,$db);
			$fp = fopen( $EDI_MsgPending . '/EDI_INV_' . $EDITransNo , 'w');

			while ($LineDetails = DB_fetch_array($MessageLinesResult)){

				if ($LineDetails['Section']=='Heading'){
					$MsgLineText = $LineDetails['LineText'];
					include ('includes/EDIVariableSubstitution.inc');
					$LastLine ='Heading';
				} elseif ($LineDetails['Section']=='Summary' AND $LastLine=='Heading') {
					/*This must be the detail section
					need to get the line details for the invoice or credit note
					for creating the detail lines */

					if ($TransDetail['Type']==10){ /*its an invoice */
						 $sql = "SELECT StockMoves.StockID,
						 		StockMaster.Description,
								-StockMoves.Qty AS Quantity,
								StockMoves.DiscountPercent,
								((1 - StockMoves.DiscountPercent) * StockMoves.Price * " . $ExchRate . "* -StockMoves.Qty) AS FxNet,
								(StockMoves.Price * " . $ExchRate . ") AS FxPrice,
								StockMoves.TaxRate,
								StockMaster.Units
							FROM StockMoves,
								StockMaster
							WHERE StockMoves.StockID = StockMaster.StockID
							AND StockMoves.Type=10
							AND StockMoves.TransNo=" . $TransNo . "
							AND StockMoves.Show_On_Inv_Crds=1";
					} else {
					/* credit note */
			 			$sql = "SELECT StockMoves.StockID,
								StockMaster.Description,
								StockMoves.Qty AS Quantity,
								StockMoves.DiscountPercent,
								((1 - StockMoves.DiscountPercent) * StockMoves.Price * " . $ExchRate . " * StockMoves.Qty) AS FxNet,
								(StockMoves.Price * " . $ExchRate . ") AS FxPrice,
								StockMoves.TaxRate,
								StockMaster.Units
							FROM StockMoves,
								StockMaster
							WHERE StockMoves.StockID = StockMaster.StockID
							AND StockMoves.Type=11 AND StockMoves.TransNo=" . $TransNo . "
							AND StockMoves.Show_On_Inv_Crds=1";
					}
					$TransLinesResult = DB_query($sql,$db);

					$LineNumber = 0;
					while ($TransLines = DB_fetch_array($TransLinesResult)){
						/*now set up the variable values */

						$LineNumber++;
						$StockID = $TransLines['StockID'];
						$sql = "SELECT PartnerStockID
								FROM EDIItemMapping
							WHERE SuppOrCust='CUST'
							AND PartnerCode ='" . $CustDetails['DebtorNo'] . "'
							AND StockID='" . $TransLines['StockID'] . "'";

						$CustStkResult = DB_query($sql,$db);
						if (DB_num_rows($CustStkResult)==1){
							$CustStkIDRow = DB_fetch_row($CustStkResult);
							$CustStockID = $CustStkIDRow[0];
						} else {
							$CustStockID = 'Not_Known';
						}
						$ItemDescription = $TransLines['Description'];
						$QtyInvoiced = $TransLines['Quantity'];
						$LineTotalExclTax = round($TransLines['FxNet'],3);
						$UnitPrice = round( $TransLines['FxNet'] / $TransLines['Quantity'], 3);
						$LineTaxAmount = round($TransLines['TaxRate'] * $TransLines['FxNet'],3);

						/*now work through the detail line segments */
						foreach ($DetailLines as $DetailLineText) {
							$MsgLineText = $DetailLineText;
							include ('includes/EDIVariableSubstitution.inc');
						}

					}
					/*to make sure dont do the detail section again */
					$LastLine ='Summary';
					$NoLines = $LineNumber;
				} elseif ($LineDetails['Section']=='Summary'){
					$MsgLineText = $LineDetails['LineText'];
					include ('includes/EDIVariableSubstitution.inc');
				}
			} /*end while there are message lines to parse and substitute vbles for */
			fclose($fp); /*close the file at the end of each transaction */
			//DB_query("UPDATE DebtorTrans SET EDISent=1 WHERE ID=" . $TransDetails['ID'],$db);
			/*Now send the file using the customer transport */
			if ($CustDetails['EDITransport']=='email'){

				$mail = new htmlMimeMail();
				$attachment = $mail->getFile( $EDI_MsgPending . "/EDI_INV_" . $EDITransNo);
				$mail->SetSubject('EDI Invoice/Credit Note ' . $EDITransNo);
				$mail->addAttachment($attachment, 'EDI_INV_' . $EDITransNo, 'application/txt');
				$mail->setFrom($CompanyName . '<' . $CompanyRecord['Email'] . '>');
				$MessageSent = $mail->send(array($CustDetails['EDIAddress']));

				if ($MessageSent==True){
					echo '<BR><BR>';
					prnMsg(_('EDI Message') . ' ' . $EDITransNo . ' ' . _('was sucessfully emailed'),'success');
				} else {
					echo '<BR><BR>';
					prnMsg(_('EDI Message') . ' ' . $EDITransNo . _('could not be emailed to') . ' ' . $CustDetails['EDIAddress'],'error');
				}
			} else { /*it must be ftp transport */

              			// set up basic connection
              			$conn_id = ftp_connect($CustDetails['EDIAddress']); // login with username and password
              			$login_result = ftp_login($conn_id, $CustDetails['EDIServerUser'], $CustDetails['EDIServerPwd']); // check connection
              			if ((!$conn_id) || (!$login_result)) {
                  			prnMsg( _('Ftp connection has failed'). '<BR>' . _('Attempted to connect to') . ' ' . $CustDetails['EDIAddress'] . ' ' ._('for user') . ' ' . $CustDetails['EDIServerUser'],'error');
                  			include('include/footer.inc');
					exit;
              			}
              			$MessageSent = ftp_put($conn_id, $EDI_MsgPending . '/EDI_INV_' . $EDITransNo, 'EDI_INV_' . $EDITransNo, FTP_ASCII); // check upload status
              			if (!$MessageSent) {
                   			echo '<BR><BR>';
					prnMsg(_('EDI Message') . ' ' . $EDITransNo . ' ' . _('could not be sent via ftp to') .' ' . $CustDetails['EDIAddress'],'error');
                   		} else {
                   			echo '<BR><BR>';
					prnMsg( _('Successfully uploaded EDI_INV_') . $EDITransNo . ' ' . _('via ftp to') . ' ' . $CustDetails['EDIAddress'],'success');
              			} // close the FTP stream
              			ftp_quit($conn_id);
			}


			if ($MessageSent==True){ /*the email was sent sucessfully */
				/* move the sent file to sent directory */
				copy ($EDI_MsgPending . '/EDI_INV_' . $EDITransNo, $EDI_MsgSent . '/EDI_INV_' . $EDITransNo);
				unlink($EDI_MsgPending . '/EDI_INV_' . $EDITransNo);
			}

		} else {

			prnMsg( _('Cannot create EDI message since there is no EDI INVOIC message template set up for') . ' ' . $CustDetails['DebtorNo'],'error');
		} /*End if there is a message template defined for the customer invoic*/
	} /* loop around all the customer transactions to be sent */

} /*loop around all the customers enabled for EDI Invoices */

include ('includes/footer.inc');
?>

<?php

/* $Revision: 1.9 $ */

$PageSecurity =15;

include ('includes/session.inc');
include ('includes/header.inc');
include('includes/SQL_CommonFunctions.inc'); //need for EDITransNo
include('includes/htmlMimeMail.php'); // need for sending email attachments

/*Get the Customers who are enabled for EDI invoicing */
$sql = 'SELECT debtorno,
		edireference,
		editransport,
		ediaddress,
		ediserveruser,
		ediserverpwd,
		daysbeforedue,
		dayinfollowingmonth
	FROM debtorsmaster INNER JOIN paymentterms ON debtorsmaster.paymentterms=paymentterms.termsindicator
	WHERE ediinvoices=1';

$EDIInvCusts = DB_query($sql,$db);

if (DB_num_rows($EDIInvCusts)==0){
	exit;
}

while ($CustDetails = DB_fetch_array($EDIInvCusts)){

	/*Figure out if there are any unset invoices or credits for the customer */

	$sql = "SELECT debtortrans.id,
			transno,
			type,
			order_,
			trandate,
			ovgst,
			ovamount,
			ovfreight,
			ovdiscount,
			debtortrans.branchcode,
			custbranchcode,
			invtext,
			shipvia,
			rate,
			brname,
			braddress1,
			braddress2,
			braddress3,
			braddress4
		FROM debtortrans INNER JOIN custbranch ON custbranch.debtorno = debtortrans.debtorno
		AND custbranch.branchcode = debtortrans.branchcode
		WHERE (type=10 or type=11)
		AND edisent=0
		AND debtortrans.debtorno='" . $CustDetails['debtorno'] . "'";

	$ErrMsg = _('There was a problem retrieving the customer transactions because');
	$TransHeaders = DB_query($sql,$db,$ErrMsg);


	if (DB_num_rows($TransHeaders)==0){
		break; /*move on to the next EDI customer */
	}

	/*Setup the variable from the DebtorsMaster required for the message */
	$CompanyEDIReference = $_SESSION['EDIReference'];
	$CustEDIReference = $CustDetails['edireference'];
	$TaxAuthorityRef = $_SESSION['CompanyRecord']['gstno'];

	while ($TransDetails = DB_fetch_array($TransHeaders)){

/*Set up the variables that will be needed in construction of the EDI message */
		if ($TransDetails['type']==10){ /* its an invoice */
			$InvOrCrd = 388;
		} else { /* its a credit note */
			$InvOrCrd = 381;
		}
		$TransNo = $TransDetails['transno'];
		/*Always an original in this script since only non-sent transactions being processed */
		$OrigOrDup = 9;
		$TranDate = SQLDateToEDI($TransDetails['trandate']);
		$OrderNo = $TransDetails['order_'];
		$CustBranchCode = $TransDetails['custbranchcode'];
		$BranchName = $TransDetails['brname'];
		$BranchStreet =$TransDetails['braddress1'];
		$BranchCity = $TransDetails['braddress2'];
		$BranchState = $TransDetails['braddress3'];
		$ExchRate = $TransDetails['rate'];
		$TaxTotal = $TransDetails['ovgst'];

		$DatePaymentDue = ConvertToEDIDate(CalcDueDate(ConvertSQLDate($TransDetails['trandate']),$CustDetails['dayinfollowingmonth'], $CustDetails['daysbeforedue']));

		$TotalAmountExclTax = $TransDetails['ovamount']+ $TransDetails['ovfreight'] + $TransDetails['ovdiscount'];
		$TotalAmountInclTax = $TransDetails['ovamount']+ $TransDetails['ovfreight'] + $TransDetails['ovdiscount'] + $TransDetails['ovgst'];

		/* NOW ... Get the message lines
			then replace variable names with data
			write the output to a file one line at a time */

		$sql = "SELECT section, linetext FROM edimessageformat WHERE partnercode='" . $CustDetails['debtorno'] . "' AND messagetype='INVOIC' ORDER BY sequenceno";
		$ErrMsg =  _('An error occurred in getting the EDI format template for') . ' ' . $CustDetails['debtorno'] . ' ' . _('because');
		$MessageLinesResult = DB_query($sql, $db,$ErrMsg);


		if (DB_num_rows($MessageLinesResult)>0){


			$DetailLines = array();
			$ArrayCounter =0;
			While ($MessageLine = DB_fetch_array($MessageLinesResult)){
				if ($MessageLine['section']=='Detail'){
					$DetailLines[$ArrayCounter]=$MessageLine['linetext'];
					$ArrayCounter++;
				}
			}
			DB_data_seek($MessageLinesResult,0);

			$EDITransNo = GetNextTransNo(99,$db);
			$fp = fopen( $_SESSION['EDI_MsgPending'] . '/EDI_INV_' . $EDITransNo , 'w');

			while ($LineDetails = DB_fetch_array($MessageLinesResult)){

				if ($LineDetails['section']=='Heading'){
					$MsgLineText = $LineDetails['linetext'];
					include ('includes/EDIVariableSubstitution.inc');
					$LastLine ='Heading';
				} elseif ($LineDetails['section']=='Summary' AND $LastLine=='Heading') {
					/*This must be the detail section
					need to get the line details for the invoice or credit note
					for creating the detail lines */

					if ($TransDetail['type']==10){ /*its an invoice */
						 $sql = "SELECT stockmoves.stockid,
						 		stockmaster.description,
								-stockmoves.qty as quantity,
								stockmoves.discountpercent,
								((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . "* -stockmoves.qty) AS fxnet,
								(stockmoves.price * " . $ExchRate . ") AS fxprice,
								stockmoves.taxrate,
								stockmaster.units
							FROM stockmoves,
								stockmaster
							WHERE stockmoves.stockid = stockmaster.stockid
							AND stockmoves.type=10
							AND stockmoves.transno=" . $TransNo . "
							AND stockmoves.show_on_inv_crds=1";
					} else {
					/* credit note */
			 			$sql = "SELECT stockmoves.stockid,
								stockmaster.description,
								stockmoves.qty as quantity,
								stockmoves.discountpercent,
								((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . " * stockmoves.qty) as fxnet,
								(stockmoves.price * " . $ExchRate . ") AS fxprice,
								stockmoves.taxrate,
								stockmaster.units
							FROM stockmoves,
								stockmaster
							WHERE stockmoves.stockid = stockmaster.stockid
							AND stockmoves.type=11 and stockmoves.transno=" . $TransNo . "
							AND stockmoves.show_on_inv_crds=1";
					}
					$TransLinesResult = DB_query($sql,$db);

					$LineNumber = 0;
					while ($TransLines = DB_fetch_array($TransLinesResult)){
						/*now set up the variable values */

						$LineNumber++;
						$StockID = $TransLines['StockID'];
						$sql = "SELECT partnerstockid
								FROM ediitemmapping
							WHERE supporcust='CUST'
							AND partnercode ='" . $CustDetails['debtorno'] . "'
							AND stockid='" . $TransLines['stockid'] . "'";

						$CustStkResult = DB_query($sql,$db);
						if (DB_num_rows($CustStkResult)==1){
							$CustStkIDRow = DB_fetch_row($CustStkResult);
							$CustStockID = $CustStkIDRow[0];
						} else {
							$CustStockID = 'Not_Known';
						}
						$ItemDescription = $TransLines['description'];
						$QtyInvoiced = $TransLines['quantity'];
						$LineTotalExclTax = round($TransLines['fxnet'],3);
						$UnitPrice = round( $TransLines['fxnet'] / $TransLines['quantity'], 3);
						$LineTaxAmount = round($TransLines['taxrate'] * $TransLines['fxnet'],3);

						/*now work through the detail line segments */
						foreach ($DetailLines as $DetailLineText) {
							$MsgLineText = $DetailLineText;
							include ('includes/EDIVariableSubstitution.inc');
						}

					}
					/*to make sure dont do the detail section again */
					$LastLine ='Summary';
					$NoLines = $LineNumber;
				} elseif ($LineDetails['section']=='Summary'){
					$MsgLineText = $LineDetails['linetext'];
					include ('includes/EDIVariableSubstitution.inc');
				}
			} /*end while there are message lines to parse and substitute vbles for */
			fclose($fp); /*close the file at the end of each transaction */
			//DB_query("UPDATE DebtorTrans SET EDISent=1 WHERE ID=" . $TransDetails['ID'],$db);
			/*Now send the file using the customer transport */
			if ($CustDetails['editransport']=='email'){

				$mail = new htmlMimeMail();
				$attachment = $mail->getFile( $_SESSION['EDI_MsgPending'] . "/EDI_INV_" . $EDITransNo);
				$mail->SetSubject('EDI Invoice/Credit Note ' . $EDITransNo);
				$mail->addAttachment($attachment, 'EDI_INV_' . $EDITransNo, 'application/txt');
				$mail->setFrom($_SESSION['CompanyRecord']['coyname'] . '<' . $_SESSION['CompanyRecord']['email'] . '>');
				$MessageSent = $mail->send(array($CustDetails['ediaddress']));

				if ($MessageSent==True){
					echo '<BR><BR>';
					prnMsg(_('EDI Message') . ' ' . $EDITransNo . ' ' . _('was sucessfully emailed'),'success');
				} else {
					echo '<BR><BR>';
					prnMsg(_('EDI Message') . ' ' . $EDITransNo . _('could not be emailed to') . ' ' . $CustDetails['ediaddress'],'error');
				}
			} else { /*it must be ftp transport */

              			// set up basic connection
              			$conn_id = ftp_connect($CustDetails['ediaddress']); // login with username and password
              			$login_result = ftp_login($conn_id, $CustDetails['ediserveruser'], $CustDetails['ediserverpwd']); // check connection
              			if ((!$conn_id) || (!$login_result)) {
                  			prnMsg( _('Ftp connection has failed'). '<BR>' . _('Attempted to connect to') . ' ' . $CustDetails['ediaddress'] . ' ' ._('for user') . ' ' . $CustDetails['ediserveruser'],'error');
                  			include('includes/footer.inc');
					exit;
              			}
              			$MessageSent = ftp_put($conn_id, $_SESSION['EDI_MsgPending'] . '/EDI_INV_' . $EDITransNo, 'EDI_INV_' . $EDITransNo, FTP_ASCII); // check upload status
              			if (!$MessageSent) {
                   			echo '<BR><BR>';
					prnMsg(_('EDI Message') . ' ' . $EDITransNo . ' ' . _('could not be sent via ftp to') .' ' . $CustDetails['ediaddress'],'error');
                   		} else {
                   			echo '<BR><BR>';
					prnMsg( _('Successfully uploaded EDI_INV_') . $EDITransNo . ' ' . _('via ftp to') . ' ' . $CustDetails['ediaddress'],'success');
              			} // close the FTP stream
              			ftp_quit($conn_id);
			}


			if ($MessageSent==True){ /*the email was sent sucessfully */
				/* move the sent file to sent directory */
				copy ($_SESSION['EDI_MsgPending'] . '/EDI_INV_' . $EDITransNo, $_SESSION['EDI_MsgSent'] . '/EDI_INV_' . $EDITransNo);
				unlink($_SESSION['EDI_MsgPending'] . '/EDI_INV_' . $EDITransNo);
			}

		} else {

			prnMsg( _('Cannot create EDI message since there is no EDI INVOIC message template set up for') . ' ' . $CustDetails['debtorno'],'error');
		} /*End if there is a message template defined for the customer invoic*/
	} /* loop around all the customer transactions to be sent */

} /*loop around all the customers enabled for EDI Invoices */

include ('includes/footer.inc');
?>
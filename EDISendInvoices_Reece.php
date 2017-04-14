<?php
/* $Id: EDISendInvoices_Reece.php  $*/

/* $Revision: 1.9 $ */

$PageSecurity =15;

include ('includes/session.php');
include ('includes/header.php');
include('includes/SQL_CommonFunctions.inc'); //need for EDITransNo
include('includes/htmlMimeMail.php'); // need for sending email attachments

//Important: Default value for EDIsent in debtortrans should probably be 1 for non EDI customers
//updated to 0 only for EDI enabled customers. As it stands run some sql to update all existing
//transactions to EDISent = 1 for newly enabled EDI customers. If you don't do this and try to run
//this code you will create a very large number of EDI invoices.

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

$EDIInvCusts = DB_query($sql);

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
			braddress4,
			braddress5
		FROM debtortrans INNER JOIN custbranch ON custbranch.debtorno = debtortrans.debtorno
		AND custbranch.branchcode = debtortrans.branchcode
		WHERE (type=10 or type=11)
		AND edisent=0
		AND debtortrans.debtorno='" . $CustDetails['debtorno'] . "'";

	$ErrMsg = _('There was a problem retrieving the customer transactions because');
	$TransHeaders = DB_query($sql,$ErrMsg);


	if (DB_num_rows($TransHeaders)==0){
		break; /*move on to the next EDI customer */
	}

	/*Setup the variable from the DebtorsMaster required for the message */
	$CompanyEDIReference = '0' . strval($_SESSION['EDIReference']); //very annoying, but had to add leading 0
	//because our GLN had leading 0 and GetConfig.php looks for numbers and text fields, saw GLN as number and skipped 0
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
		//$TranDate = SQLDateToEDI($TransDetails['trandate']);
		$TranDate = date('Ymd');  //probably should use the date edi was created not the date filed in our system
		$TranDateTime = date('Ymd:hi');
		$OrderNo = $TransDetails['order_'];
		$CustBranchCode = $TransDetails['branchcode'];
		$BranchName = $TransDetails['brname'];
		$BranchStreet =$TransDetails['braddress1'];
		$BranchSuburb = $TransDetails['braddress2'];
		$BranchState = $TransDetails['braddress3'];
		$BranchZip = $TransDetails['braddress4'];
		$BranchCountry = $TransDetails['braddress5'];
		$ExchRate = $TransDetails['rate'];
		$TaxTotal = number_format($TransDetails['ovgst'],2, '.', '');
		$ShipToFreight = number_format(round($TransDetails['ovfreight'],2),2, '.', '');
		$SegCount = 1;


		$DatePaymentDue = ConvertToEDIDate(CalcDueDate(ConvertSQLDate($TransDetails['trandate']),$CustDetails['dayinfollowingmonth'], $CustDetails['daysbeforedue']));

		$TotalAmountExclTax = number_format(($TransDetails['ovamount']+ $TransDetails['ovfreight'] + $TransDetails['ovdiscount']),2, '.', '');
		$TotalAmountInclTax = number_format(($TransDetails['ovamount']+ $TransDetails['ovfreight'] + $TransDetails['ovdiscount'] + $TransDetails['ovgst']),2, '.', '');

		//**************Need to get delivery address as may be diff from branch address

		$sql = "SELECT deliverto,
				deladd1,
				deladd2,
				deladd3,
				deladd4,
				deladd5,
				deladd6,
				salesorders.customerref
				FROM debtortrans INNER JOIN salesorders ON debtortrans.order_ = salesorders.orderno
				WHERE order_ = '" . $OrderNo . "'";

				$ErrMsg = _('There was a problem retrieving the ship to details because');
				$ShipToLines = DB_query($sql,$ErrMsg);

				While ($ShipTo = DB_fetch_array($ShipToLines)){
					$ShipToName = $ShipTo[0];
					$ShipToStreet = $ShipTo[1];
					$ShipToSuburb = $ShipTo[2];
					$ShipToState = $ShipTo[3];
					$ShipToZip = $ShipTo[4];
					$ShipToCountry = $ShipTo[5];
					$CustOrderNo = $ShipTo[7];

				}

		//**************Need to get delivery address as may be diff from branch address

		//**************Reece needs NAD ST in every invoice, sometimes freeform text, so no real code

		if($ShipToName === $BranchName){
			$ShipToCode = $CustBranchCode;
		} Else {
			$ShipToCode = $ShipToName;
		}

		//**************Reece needs NAD ST in every invoice, sometimes freeform text, so no real code

		//**************Taxrate, need to find

		$sql = "SELECT 	stockmovestaxes.taxrate
	                        FROM stockmoves,
							stockmovestaxes
	                        WHERE stockmoves.stkmoveno = stockmovestaxes.stkmoveno
	                        AND stockmoves.transno=" . $TransNo . "
	                        AND stockmoves.show_on_inv_crds=1
	                        LIMIT 0,1";

		                $ResultTax = DB_query($sql);

		                $TaxRate = 100 * (mysql_result($ResultTax, 0));

                //**************Taxrate, need to find

		//**************Check to see if freight was added, probably specific to Reece and some other OZ hardware stores

		        if($ShipToFreight > 0){
		                $FreightTax = number_format(round(($ShipToFreight * $TaxRate/100),2),2, '.', '');
		                $Freight_YN = "ALC+C" . "'" . "MOA+64:" .$ShipToFreight. "'" . "TAX+7+GST+++:::" .$TaxRate. "'". "MOA+124:" .$FreightTax."'";
		                $SegCount = $SegCount + 3;
						} else {
		                $Freight_YN = "";
		                }

		//**************Check to see if freight was added could do this in Substitution, skip if 0 freight



		//Get the message lines, replace variable names with data, write the output to a file one line at a time

		$sql = "SELECT section, linetext FROM edimessageformat WHERE partnercode='" . $CustDetails['debtorno'] . "' AND messagetype='INVOIC' ORDER BY sequenceno";
		$ErrMsg =  _('An error occurred in getting the EDI format template for') . ' ' . $CustDetails['debtorno'] . ' ' . _('because');
		$MessageLinesResult = DB_query($sql,$ErrMsg);


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
			$fp = fopen('EDI_INV_' . $TransNo . '.txt', 'w');

			while ($LineDetails = DB_fetch_array($MessageLinesResult)){

				if ($LineDetails['section']=='Heading'){
					$MsgLineText = $LineDetails['linetext'];
					include ('includes/EDIVariableSubstitution.inc');
					$LastLine ='Heading';
					}

				if ($LineDetails['section']=='Detail' AND $LastLine=='Heading') {
					/*This must be the detail section
					need to get the line details for the invoice or credit note
					for creating the detail lines */


					if ($TransDetails['type']==10){ /*its an invoice */
						 $sql = "SELECT stockmoves.stockid,
						 		stockmaster.description,
								-stockmoves.qty as quantity,
								stockmoves.discountpercent,
								((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . " * -stockmoves.qty) AS fxnet,
								(stockmoves.price * " . $ExchRate . ") AS fxprice,
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
								stockmaster.units
							FROM stockmoves,
								stockmaster
							WHERE stockmoves.stockid = stockmaster.stockid
							AND stockmoves.type=11 and stockmoves.transno=" . $TransNo . "
							AND stockmoves.show_on_inv_crds=1";
					}
					$TransLinesResult = DB_query($sql);

					$LineNumber = 0;
					while ($TransLines = DB_fetch_array($TransLinesResult)){
						/*now set up the variable values */

						$LineNumber++;
						$StockID = $TransLines['stockid'];
						$sql = "SELECT partnerstockid
								FROM ediitemmapping
							WHERE supporcust='CUST'
							AND partnercode ='" . $CustDetails['debtorno'] . "'
							AND stockid='" . $TransLines['stockid'] . "'";

						$CustStkResult = DB_query($sql);
						if (DB_num_rows($CustStkResult)==1){
							$CustStkIDRow = DB_fetch_row($CustStkResult);
							$CustStockID = $CustStkIDRow[0];
						} else {
							$CustStockID = 'Not_Known';
						}
						$ItemDescription = $TransLines['description'];
						$QtyInvoiced = $TransLines['quantity'];
						$LineTotalExclTax = number_format(round($TransLines['fxnet'],3),2, '.', '');
						$UnitPriceExclTax = number_format(round( $TransLines['fxnet'] / $TransLines['quantity'], 3),2, '.', '');
						$LineTaxAmount = number_format(round($TaxRate/100 * $TransLines['fxnet'],3),2, '.', '');
						$LineTotalInclTax = number_format(round((1+$TaxRate/100) * $LineTotalExclTax,3),2, '.', '');
						$UnitPriceInclTax = number_format(round((1+$TaxRate/100) * $UnitPriceExclTax,2),2, '.', '');

						/*now work through the detail line segments */
						foreach ($DetailLines as $DetailLineText) {
							$MsgLineText = $DetailLineText;
							include ('includes/EDIVariableSubstitution.inc');
						}


					}


					$LastLine ='Detail';
					$NoLines = $LineNumber;
					}

					if($LineDetails['section']=='Summary' AND $LastLine=='Detail'){
					$MsgLineText = $LineDetails['linetext'];
					include ('includes/EDIVariableSubstitution.inc');
					}
			} /*end while there are message lines to parse and substitute vbles for */
			fclose($fp); /*close the file at the end of each transaction */
			DB_query("UPDATE debtortrans SET EDISent=1 WHERE ID=" . $TransDetails['id']);
			/*Now send the file using the customer transport */
			if ($CustDetails['editransport']=='email'){

				$mail = new htmlMimeMail();
				$attachment = $mail->getFile( "EDI_INV_" . $TransNo .".txt");
				$mail->SetSubject('EDI Invoice/Credit Note ' . $TransNo);
				$mail->addAttachment($attachment, 'EDI_INV_' . $TransNo . '.txt', 'application/txt');
				if($_SESSION['SmtpSetting']==0){
					$mail->setFrom($_SESSION['CompanyRecord']['coyname'] . '<' . $_SESSION['CompanyRecord']['email'] . '>');
					$MessageSent = $mail->send(array($CustDetails['ediaddress']));
				}else{
					$MessageSent = SendmailBySmtp($mail,array($CustDetails['ediaddress']));
				}


				if ($MessageSent==True){
					echo '<BR><BR>';
					prnMsg(_('EDI Message') . ' ' . $TransNo . ' ' . _('was sucessfully emailed'),'success');
				} else {
					echo '<BR><BR>';
					prnMsg(_('EDI Message') . ' ' . $TransNo . _('could not be emailed to') . ' ' . $CustDetails['ediaddress'],'error');
				}
			} else { /*it must be ftp transport */

						 //Godaddy limitations make it impossible to sftp using ssl or curl, so save to EDI_Sent file and 'rsynch' back to sftp server

              			/* set up basic connection
              			$conn_id = ftp_connect($CustDetails['ediaddress']); // login with username and password
              			$login_result = ftp_login($conn_id, $CustDetails['ediserveruser'], $CustDetails['ediserverpwd']); // check connection
              			if ((!$conn_id) || (!$login_result)) {
                  			prnMsg( _('Ftp connection has failed'). '<BR>' . _('Attempted to connect to') . ' ' . $CustDetails['ediaddress'] . ' ' ._('for user') . ' ' . $CustDetails['ediserveruser'],'error');
                  			include('includes/footer.php');
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
              			*/
			}


			if ($MessageSent==True){ /*the email was sent sucessfully */
				/* move the sent file to sent directory */
				$source = 'EDI_INV_' . $TransNo . '.txt';
                                $destination = 'EDI_Sent/EDI_INV_' . $TransNo . '.txt';
                                rename($source, $destination);

			}

		} else {

			prnMsg( _('Cannot create EDI message since there is no EDI INVOIC message template set up for') . ' ' . $CustDetails['debtorno'],'error');
		} /*End if there is a message template defined for the customer invoic*/


	} /* loop around all the customer transactions to be sent */

} /*loop around all the customers enabled for EDI Invoices */

include ('includes/footer.php');
?>

<?php

$PageSecurity = 15;

require(__DIR__ . '/includes/session.php');

$ViewTopic = 'EDI';
$BookMark = '';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php'); // need for EDITransNo

// Important: Default value for EDIsent in debtortrans should probably be 1 for non EDI customers
// updated to 0 only for EDI enabled customers. As it stands run some sql to update all existing
// transactions to EDIsent = 1 for newly enabled EDI customers. If you don't do this and try to run
// this code you will create a very large number of EDI invoices.

/*Get the Customers who are enabled for EDI invoicing */
$SQL = 'SELECT debtorno,
		edireference,
		editransport,
		ediaddress,
		ediserveruser,
		ediserverpwd,
		daysbeforedue,
		dayinfollowingmonth
	FROM debtorsmaster INNER JOIN paymentterms ON debtorsmaster.paymentterms=paymentterms.termsindicator
	WHERE ediinvoices=1';

$EDIInvCusts = DB_query($SQL);

if (DB_num_rows($EDIInvCusts) == 0) {
	exit();
}

while ($CustDetails = DB_fetch_array($EDIInvCusts)) {

	/*Figure out if there are any unset invoices or credits for the customer */

	$SQL = "SELECT debtortrans.id,
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

	$ErrMsg = __('There was a problem retrieving the customer transactions because');
	$TransHeaders = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($TransHeaders) == 0) {
		break; /*move on to the next EDI customer */
	}

	/*Setup the variable from the DebtorsMaster required for the message */
	$CompanyEDIReference = '0' . strval($_SESSION['EDIReference']); // very annoying, but had to add leading 0
	// because our GLN had leading 0 and GetConfig.php looks for numbers and text fields, saw GLN as number and skipped 0
	$CustEDIReference = $CustDetails['edireference'];
	$TaxAuthorityRef = $_SESSION['CompanyRecord']['gstno'];

	while ($TransDetails = DB_fetch_array($TransHeaders)) {

		/*Set up the variables that will be needed in construction of the EDI message */
		if ($TransDetails['type'] == 10) { /* its an invoice */
			$InvOrCrd = 388;
		} else { /* its a credit note */
			$InvOrCrd = 381;
		}
		$TransNo = $TransDetails['transno'];
		/*Always an original in this script since only non-sent transactions being processed */
		$OrigOrDup = 9;
		// $TranDate = SQLDateToEDI($TransDetails['trandate']);
		$TranDate = date('Ymd'); // probably should use the date edi was created not the date filed in our system
		$TranDateTime = date('Ymd:hi');
		$OrderNo = $TransDetails['order_'];
		$CustBranchCode = $TransDetails['branchcode'];
		$BranchName = $TransDetails['brname'];
		$BranchStreet = $TransDetails['braddress1'];
		$BranchSuburb = $TransDetails['braddress2'];
		$BranchState = $TransDetails['braddress3'];
		$BranchZip = $TransDetails['braddress4'];
		$BranchCountry = $TransDetails['braddress5'];
		$ExchRate = $TransDetails['rate'];
		$TaxTotal = number_format($TransDetails['ovgst'], 2, '.', '');
		$ShipToFreight = number_format(round($TransDetails['ovfreight'], 2), 2, '.', '');
		$SegCount = 1;

		$DatePaymentDue = ConvertToEDIDate(CalcDueDate(ConvertSQLDate($TransDetails['trandate']),
			$CustDetails['dayinfollowingmonth'], $CustDetails['daysbeforedue']));

		$TotalAmountExclTax = number_format(($TransDetails['ovamount'] + $TransDetails['ovfreight'] +
			$TransDetails['ovdiscount']), 2, '.', '');
		$TotalAmountInclTax = number_format(($TransDetails['ovamount'] + $TransDetails['ovfreight'] +
			$TransDetails['ovdiscount'] + $TransDetails['ovgst']), 2, '.', '');

		// **************Need to get delivery address as may be diff from branch address

		$SQL = "SELECT deliverto,
				deladd1,
				deladd2,
				deladd3,
				deladd4,
				deladd5,
				deladd6,
				salesorders.customerref
				FROM debtortrans INNER JOIN salesorders ON debtortrans.order_ = salesorders.orderno
				WHERE order_ = '" . $OrderNo . "'";

				$ErrMsg = __('There was a problem retrieving the ship to details because');
				$ShipToLines = DB_query($SQL, $ErrMsg);

				while ($ShipTo = DB_fetch_array($ShipToLines)) {
					$ShipToName = $ShipTo[0];
					$ShipToStreet = $ShipTo[1];
					$ShipToSuburb = $ShipTo[2];
					$ShipToState = $ShipTo[3];
					$ShipToZip = $ShipTo[4];
					$ShipToCountry = $ShipTo[5];
					$CustOrderNo = $ShipTo[7];
				}

		// **************Need to get delivery address as may be diff from branch address

		// **************Reece needs NAD ST in every invoice, sometimes freeform text, so no real code

		if ($ShipToName === $BranchName) {
			$ShipToCode = $CustBranchCode;
		} else {
			$ShipToCode = $ShipToName;
		}

		// **************Reece needs NAD ST in every invoice, sometimes freeform text, so no real code

		// **************Taxrate, need to find

		$SQL = "SELECT stockmovestaxes.taxrate
	                        FROM stockmoves,
							stockmovestaxes
	                        WHERE stockmoves.stkmoveno = stockmovestaxes.stkmoveno
	                        AND stockmoves.transno=" . $TransNo . "
	                        AND stockmoves.show_on_inv_crds=1
	                        LIMIT 0,1";

		$ResultTax = DB_query($SQL);
		if (DB_num_rows($ResultTax) > 0) {
			$TaxRow = DB_fetch_array($ResultTax);
			$TaxRate = 100 * ($TaxRow['taxrate']);
		} else {
			$TaxRate = 0;
		}

		// **************Taxrate, need to find

		// **************Check to see if freight was added, probably specific to Reece and some other OZ hardware stores

		if ($ShipToFreight > 0) {
			$FreightTax = number_format(round(($ShipToFreight * $TaxRate / 100), 2), 2, '.', '');
			$Freight_YN = "ALC+C" . "'" . "MOA+64:" . $ShipToFreight . "'" . "TAX+7+GST+++:::" . $TaxRate . "'" .
				"MOA+124:" . $FreightTax . "'";
			$SegCount = $SegCount + 3;
		} else {
			$Freight_YN = "";
		}

		// **************Check to see if freight was added could do this in Substitution, skip if 0 freight

		// Get the message lines, replace variable names with data, write the output to a file one line at a time

		$SQL = "SELECT section, linetext FROM edimessageformat WHERE partnercode='" . $CustDetails['debtorno'] .
			"' AND messagetype='INVOIC' ORDER BY sequenceno";
		$ErrMsg = __('An error occurred in getting the EDI format template for') . ' ' . $CustDetails['debtorno'] . ' ' .
			__('because');
		$MessageLinesResult = DB_query($SQL, $ErrMsg);

		if (DB_num_rows($MessageLinesResult) > 0) {
			$DetailLines = array();
			$ArrayCounter = 0;
			while ($MessageLine = DB_fetch_array($MessageLinesResult)) {
				if ($MessageLine['section'] == 'Detail') {
					$DetailLines[$ArrayCounter] = $MessageLine['linetext'];
					$ArrayCounter++;
				}
			}
			DB_data_seek($MessageLinesResult, 0);

			$EDITransNo = GetNextTransNo(99);

			/// @todo is there a better dir than this? eg. the sys temp dir?
			$fp = fopen($PathPrefix . 'EDI_INV_' . $TransNo . '.txt', 'w');

			while ($LineDetails = DB_fetch_array($MessageLinesResult)) {

				if ($LineDetails['section'] == 'Heading') {
					$MsgLineText = $LineDetails['linetext'];
					include('includes/EDIVariableSubstitution.php');
					$LastLine = 'Heading';
				}

				if ($LineDetails['section'] == 'Detail' AND $LastLine == 'Heading') {
					/*This must be the detail section
					need to get the line details for the invoice or credit note
					for creating the detail lines */

					if ($TransDetails['type'] == 10) { /*its an invoice */
						$SQL = "SELECT stockmoves.stockid,
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
			 			$SQL = "SELECT stockmoves.stockid,
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
					$TransLinesResult = DB_query($SQL);

					$LineNumber = 0;
					while ($TransLines = DB_fetch_array($TransLinesResult)) {
						/*now set up the variable values */

						$LineNumber++;
						$StockID = $TransLines['stockid'];
						$SQL = "SELECT partnerstockid
								FROM ediitemmapping
							WHERE supporcust='CUST'
							AND partnercode ='" . $CustDetails['debtorno'] . "'
							AND stockid='" . $TransLines['stockid'] . "'";

						$CustStkResult = DB_query($SQL);
						if (DB_num_rows($CustStkResult) == 1) {
							$CustStkIDRow = DB_fetch_row($CustStkResult);
							$CustStockID = $CustStkIDRow[0];
						} else {
							$CustStockID = 'Not_Known';
						}
						$ItemDescription = $TransLines['description'];
						$QtyInvoiced = $TransLines['quantity'];
						$LineTotalExclTax = number_format(round($TransLines['fxnet'], 3), 2, '.', '');
						$UnitPriceExclTax = number_format(round($TransLines['fxnet'] / $TransLines['quantity'], 3),
							2, '.', '');
						$LineTaxAmount = number_format(round($TaxRate / 100 * $TransLines['fxnet'], 3), 2, '.', '');
						$LineTotalInclTax = number_format(round((1 + $TaxRate / 100) * $LineTotalExclTax, 3),
							2, '.', '');
						$UnitPriceInclTax = number_format(round((1 + $TaxRate / 100) * $UnitPriceExclTax, 2),
							2, '.', '');

						/*now work through the detail line segments */
						foreach ($DetailLines as $DetailLineText) {
							$MsgLineText = $DetailLineText;
							include('includes/EDIVariableSubstitution.php');
						}
					}

					$LastLine = 'Detail';
					$NoLines = $LineNumber;
				}

				if ($LineDetails['section'] == 'Summary' AND $LastLine == 'Detail') {
					$MsgLineText = $LineDetails['linetext'];
					include('includes/EDIVariableSubstitution.php');
				}
			} /*end while there are message lines to parse and substitute vbles for */
			fclose($fp); /*close the file at the end of each transaction */
			DB_query("UPDATE debtortrans SET EDISent=1 WHERE ID=" . $TransDetails['id']);
			/*Now send the file using the customer transport */
			if ($CustDetails['editransport'] == 'email') {
				$MessageSent = SendEmailFromWebERP($_SESSION['CompanyRecord']['coyname'] . "<" .
					$_SESSION['CompanyRecord']['email'] . ">",
					$CustDetails['ediaddress'],
					'EDI Invoice/Credit Note ' . $TransNo,
					'',
					"EDI_INV_" . $TransNo . ".txt",
					false);

				if ($MessageSent == true) {
					echo '<BR><BR>';
					prnMsg(__('EDI Message') . ' ' . $TransNo . ' ' . __('was sucessfully emailed'), 'success');
				} else {
					echo '<BR><BR>';
					prnMsg(__('EDI Message') . ' ' . $TransNo . __('could not be emailed to') . ' ' .
						$CustDetails['ediaddress'], 'error');
				}
			} else { /*it must be ftp transport */

				// Godaddy limitations make it impossible to sftp using ssl or curl, so save to EDI_Sent file and
				// 'rsynch' back to sftp server

				/* set up basic connection
				$conn_id = ftp_connect($CustDetails['ediaddress']); // login with username and password
				$login_result = ftp_login($conn_id, $CustDetails['ediserveruser'], $CustDetails['ediserverpwd']);
				// check connection
				if ((!$conn_id) || (!$login_result)) {
					prnMsg( __('Ftp connection has failed'). '<BR>' . __('Attempted to connect to') . ' ' .
						$CustDetails['ediaddress'] . ' ' .__('for user') . ' ' . $CustDetails['ediserveruser'],'error');
					include('includes/footer.php');
					exit();
				}
				$MessageSent = ftp_put($conn_id, $_SESSION['EDI_MsgPending'] . '/EDI_INV_' . $EDITransNo,
					'EDI_INV_' . $EDITransNo, FTP_ASCII); // check upload status
				if (!$MessageSent) {
					echo '<BR><BR>';
					prnMsg(__('EDI Message') . ' ' . $EDITransNo . ' ' . __('could not be sent via ftp to') .' ' .
						$CustDetails['ediaddress'],'error');
				} else {
					echo '<BR><BR>';
					prnMsg( __('Successfully uploaded EDI_INV_') . $EDITransNo . ' ' . __('via ftp to') . ' ' .
						$CustDetails['ediaddress'],'success');
				} // close the FTP stream
				ftp_quit($conn_id);
				*/
			}

			if ($MessageSent == true) { /*the email was sent successfully */
				/* move the sent file to sent directory */
				$Source = $PathPrefix . 'EDI_INV_' . $TransNo . '.txt';
				$destination = $PathPrefix . 'EDI_Sent/EDI_INV_' . $TransNo . '.txt';
				rename($Source, $destination);
			}

		} else {
			prnMsg(__('Cannot create EDI message since there is no EDI INVOIC message template set up for') . ' ' .
				$CustDetails['debtorno'], 'error');
		} /*End if there is a message template defined for the customer invoic*/

	} /* loop around all the customer transactions to be sent */

} /*loop around all the customers enabled for EDI Invoices */

include('includes/footer.php');

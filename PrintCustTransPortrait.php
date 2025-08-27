<?php

/*  Print Invoices or Credit Notes (Portrait Mode) */

require(__DIR__ . '/includes/session.php');

$Title = __('Print Invoices or Credit Notes (Portrait Mode)');
$ViewTopic = 'ARReports';
$BookMark = 'PrintInvoicesCredits';

if(isset($_GET['FromTransNo'])) {
	$FromTransNo = filter_number_format($_GET['FromTransNo']);
} elseif(isset($_POST['FromTransNo'])) {
	$FromTransNo = filter_number_format($_POST['FromTransNo']);
} else {
	$FromTransNo = '';
}

if(isset($_GET['InvOrCredit'])) {
	$InvOrCredit = $_GET['InvOrCredit'];
} elseif(isset($_POST['InvOrCredit'])) {
	$InvOrCredit = $_POST['InvOrCredit'];
}

if(isset($_GET['PrintPDF'])) {
	$PrintPDF = $_GET['PrintPDF'];
} elseif(isset($_POST['PrintPDF'])) {
	$PrintPDF = $_POST['PrintPDF'];
}

if(!isset($_POST['ToTransNo'])
	OR trim($_POST['ToTransNo'])==''
	OR filter_number_format($_POST['ToTransNo']) < $FromTransNo) {

	$_POST['ToTransNo'] = $FromTransNo;
}

$FirstTrans = $FromTransNo; /* Need to start a new page only on subsequent transactions */

if(isset($PrintPDF)
	and $PrintPDF!=''
	and isset($FromTransNo)
	and isset($InvOrCredit)
	and $FromTransNo!='') {

	include('includes/PDFStarter.php');

	if($InvOrCredit=='Invoice') {
		$pdf->addInfo('Title',__('Sales Invoice') . ' ' . $FromTransNo . ' ' . __('to') . ' ' . $_POST['ToTransNo']);
		$pdf->addInfo('Subject',__('Invoices from') . ' ' . $FromTransNo . ' ' . __('to') . ' ' . $_POST['ToTransNo']);
	} else {
		$pdf->addInfo('Title',__('Sales Credit Note') );
		$pdf->addInfo('Subject',__('Credit Notes from') . ' ' . $FromTransNo . ' ' . __('to') . ' ' . $_POST['ToTransNo']);
	}

	$FirstPage = true;
	$LineHeight=16;

	//Keep a record of the user's language
	$UserLanguage = $_SESSION['Language'];

	while($FromTransNo <= filter_number_format($_POST['ToTransNo'])) {

	/*retrieve the invoice details from the database to print
	notice that salesorder record must be present to print the invoice purging of sales orders will
	nobble the invoice reprints */

	// check if the user has set a default bank account for invoices, if not leave it blank
		$SQL = "SELECT bankaccounts.invoice,
					bankaccounts.bankaccountnumber,
					bankaccounts.bankaccountcode
				FROM bankaccounts
				WHERE bankaccounts.invoice = '1'";
		$Result = DB_query($SQL, '', '', false, false);
		if(DB_error_no()!=1) {
			if(DB_num_rows($Result)==1) {
				$MyRow = DB_fetch_array($Result);
				$DefaultBankAccountNumber = __('Account') .': ' .$MyRow['bankaccountnumber'];
				$DefaultBankAccountCode = __('Bank Code:') .' ' .$MyRow['bankaccountcode'];
			} else {
				$DefaultBankAccountNumber = '';
				$DefaultBankAccountCode = '';
			}
		} else {
			$DefaultBankAccountNumber = '';
			$DefaultBankAccountCode = '';
		}
// gather the invoice data

		if($InvOrCredit=='Invoice') {
			$SQL = "SELECT debtortrans.trandate,
							debtortrans.ovamount,
							debtortrans.ovdiscount,
							debtortrans.ovfreight,
							debtortrans.ovgst,
							debtortrans.rate,
							debtortrans.invtext,
							debtortrans.consignment,
							debtortrans.packages,
							debtorsmaster.name,
							debtorsmaster.address1,
							debtorsmaster.address2,
							debtorsmaster.address3,
							debtorsmaster.address4,
							debtorsmaster.address5,
							debtorsmaster.address6,
							debtorsmaster.currcode,
							debtorsmaster.invaddrbranch,
							debtorsmaster.taxref,
							debtorsmaster.language_id,
							paymentterms.terms,
							paymentterms.dayinfollowingmonth,
							paymentterms.daysbeforedue,
							salesorders.deliverto,
							salesorders.deladd1,
							salesorders.deladd2,
							salesorders.deladd3,
							salesorders.deladd4,
							salesorders.deladd5,
							salesorders.deladd6,
							salesorders.customerref,
							salesorders.orderno,
							salesorders.orddate,
							locations.locationname,
							shippers.shippername,
							custbranch.brname,
							custbranch.braddress1,
							custbranch.braddress2,
							custbranch.braddress3,
							custbranch.braddress4,
							custbranch.braddress5,
							custbranch.braddress6,
							custbranch.brpostaddr1,
							custbranch.brpostaddr2,
							custbranch.brpostaddr3,
							custbranch.brpostaddr4,
							custbranch.brpostaddr5,
							custbranch.brpostaddr6,
							custbranch.salesman,
							salesman.salesmanname,
							debtortrans.debtorno,
							debtortrans.branchcode,
							currencies.decimalplaces
						FROM debtortrans INNER JOIN debtorsmaster
						ON debtortrans.debtorno=debtorsmaster.debtorno
						INNER JOIN custbranch
						ON debtortrans.debtorno=custbranch.debtorno
						AND debtortrans.branchcode=custbranch.branchcode
						INNER JOIN salesorders
						ON debtortrans.order_ = salesorders.orderno
						INNER JOIN shippers
						ON debtortrans.shipvia=shippers.shipper_id
						INNER JOIN salesman
						ON custbranch.salesman=salesman.salesmancode
						INNER JOIN locations
						ON salesorders.fromstkloc=locations.loccode
						INNER JOIN locationusers
						ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1
						INNER JOIN paymentterms
						ON debtorsmaster.paymentterms=paymentterms.termsindicator
						INNER JOIN currencies
						ON debtorsmaster.currcode=currencies.currabrev
						WHERE debtortrans.type=10
						AND debtortrans.transno='" . $FromTransNo . "'";

			if(isset($_POST['PrintEDI']) and $_POST['PrintEDI']=='No') {
				$SQL = $SQL . ' AND debtorsmaster.ediinvoices=0';
			}
		} else {/* then its a credit note */
			$SQL = "SELECT debtortrans.trandate,
							debtortrans.ovamount,
							debtortrans.ovdiscount,
							debtortrans.ovfreight,
							debtortrans.ovgst,
							debtortrans.rate,
							debtortrans.invtext,
							debtorsmaster.invaddrbranch,
							debtorsmaster.name,
							debtorsmaster.address1,
							debtorsmaster.address2,
							debtorsmaster.address3,
							debtorsmaster.address4,
							debtorsmaster.address5,
							debtorsmaster.address6,
							debtorsmaster.currcode,
							debtorsmaster.taxref,
							debtorsmaster.language_id,
							custbranch.brname,
							custbranch.braddress1,
							custbranch.braddress2,
							custbranch.braddress3,
							custbranch.braddress4,
							custbranch.braddress5,
							custbranch.braddress6,
							custbranch.brpostaddr1,
							custbranch.brpostaddr2,
							custbranch.brpostaddr3,
							custbranch.brpostaddr4,
							custbranch.brpostaddr5,
							custbranch.brpostaddr6,
							custbranch.salesman,
							salesman.salesmanname,
							debtortrans.debtorno,
							debtortrans.branchcode,
							currencies.decimalplaces
						FROM debtortrans INNER JOIN debtorsmaster
						ON debtortrans.debtorno=debtorsmaster.debtorno
						INNER JOIN custbranch
						ON debtortrans.debtorno=custbranch.debtorno
						AND debtortrans.branchcode=custbranch.branchcode
						INNER JOIN salesman
						ON custbranch.salesman=salesman.salesmancode
						INNER JOIN currencies
						ON debtorsmaster.currcode=currencies.currabrev
						WHERE debtortrans.type=11
						AND debtortrans.transno='" . $FromTransNo . "'";


			if(isset($_POST['PrintEDI']) and $_POST['PrintEDI']=='No') {
				$SQL = $SQL . ' AND debtorsmaster.ediinvoices=0';
			}
		}

		$ErrMsg = __('There was a problem retrieving the invoice or credit note details for note number') . ' ' . $InvoiceToPrint;
		$Result = DB_query($SQL, $ErrMsg);

		if(DB_num_rows($Result)==1) {
			$MyRow = DB_fetch_array($Result);

			if ( $_SESSION['SalesmanLogin'] != '' AND $_SESSION['SalesmanLogin'] != $MyRow['salesman'] ) {
				$Title=__('Select Invoices/Credit Notes To Print');
				include('includes/header.php');
				prnMsg(__('Your account is set up to see only a specific salespersons orders. You are not authorised to view transaction for this order'),'error');
				include('includes/footer.php');
				exit();
			}
			if ( $CustomerLogin == 1 AND $MyRow['debtorno'] != $_SESSION['CustomerID'] ) {
				$Title=__('Select Invoices/Credit Notes To Print');
				include('includes/header.php');
				echo '<p class="bad">' . __('This transaction is addressed to another customer and cannot be displayed for privacy reasons') . '. ' . __('Please select only transactions relevant to your company').'</p>';
				include('includes/footer.php');
				exit();
			}

			$ExchRate = $MyRow['rate'];

			// Change the language to the customer's language
			/// @todo use a better way to achieve that than setting a value into the session and including a file
			$_SESSION['Language'] = $MyRow['language_id'];
			include($PathPrefix . 'includes/LanguageSetup.php');

			if($InvOrCredit == 'Invoice') {
				$SQL = "SELECT stockmoves.stockid,
						stockmaster.description,
						-stockmoves.qty as quantity,
						stockmoves.discountpercent,
						((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . "* -stockmoves.qty) AS fxnet,
						(stockmoves.price * " . $ExchRate . ") AS fxprice,
						stockmoves.narrative,
						stockmaster.controlled,
						stockmaster.serialised,
						stockmaster.units,
						stockmoves.stkmoveno,
						stockmaster.decimalplaces
					FROM stockmoves INNER JOIN stockmaster
					ON stockmoves.stockid = stockmaster.stockid
					WHERE stockmoves.type=10
					AND stockmoves.transno='" . $FromTransNo . "'
					AND stockmoves.show_on_inv_crds=1";
			} else {
				/* only credit notes to be retrieved */
				$SQL = "SELECT stockmoves.stockid,
						stockmaster.description,
						stockmoves.qty as quantity,
						stockmoves.discountpercent,
						((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . " * stockmoves.qty) AS fxnet,
						(stockmoves.price * " . $ExchRate . ") AS fxprice,
						stockmoves.narrative,
						stockmaster.controlled,
						stockmaster.serialised,
						stockmaster.units,
						stockmoves.stkmoveno,
						stockmaster.decimalplaces
					FROM stockmoves INNER JOIN stockmaster
					ON stockmoves.stockid = stockmaster.stockid
					WHERE stockmoves.type=11
					AND stockmoves.transno='" . $FromTransNo . "'
					AND stockmoves.show_on_inv_crds=1";
			} // end else

		$ErrMsg = __('There was a problem retrieving the invoice or credit note stock movement details for invoice number') . ' ' . $FromTransNo;
		$Result = DB_query($SQL, $ErrMsg);

		if ($InvOrCredit=='Invoice') {
			/* Calculate Due Date info. This reference is used in the PDFTransPageHeaderPortrait.php file. */
			$DisplayDueDate = CalcDueDate(ConvertSQLDate($MyRow['trandate']), $MyRow['dayinfollowingmonth'], $MyRow['daysbeforedue']);
		}

		if(DB_num_rows($Result)>0) {

			$FontSize = 10;
			$PageNumber = 1;

			include('includes/PDFTransPageHeaderPortrait.php');
			$FirstPage = false;

			while($MyRow2=DB_fetch_array($Result)) {
				if($MyRow2['discountpercent'] == 0) {
					$DisplayDiscount = '';
				} else {
					$DisplayDiscount = locale_number_format($MyRow2['discountpercent'] * 100, 2) . '%';
					$DiscountPrice = $MyRow2['fxprice'] * (1 - $MyRow2['discountpercent']);
				}
				$DisplayNet = locale_number_format($MyRow2['fxnet'],$MyRow['decimalplaces']);
				$DisplayPrice = locale_number_format($MyRow2['fxprice'],$MyRow['decimalplaces']);
				$DisplayQty = locale_number_format($MyRow2['quantity'],$MyRow2['decimalplaces']);

				$LeftOvers = $pdf->addTextWrap($Left_Margin+5,$YPos,71,$FontSize,$MyRow2['stockid']);// Print item code.
				//Get translation if it exists
				$TranslationResult = DB_query("SELECT descriptiontranslation
												FROM stockdescriptiontranslations
												WHERE stockid='" . $MyRow2['stockid'] . "'
												AND language_id='" . $MyRow['language_id'] ."'");

				if(DB_num_rows($TranslationResult)==1) { //there is a translation
					$TranslationRow = DB_fetch_array($TranslationResult);
					$LeftOvers = $pdf->addTextWrap($Left_Margin+80,$YPos,186,$FontSize,$TranslationRow['descriptiontranslation']);// Print translated item short description.
				} else {
					$LeftOvers = $pdf->addTextWrap($Left_Margin+80,$YPos,186,$FontSize,$MyRow2['description']);// Print item short description.
				}

				$Lines=1;
				while($LeftOvers!='') {
					$LeftOvers = $pdf->addTextWrap($Left_Margin+80,$YPos-(10*$Lines),186,$FontSize,$LeftOvers);
					$Lines++;
				}

				$LeftOvers = $pdf->addTextWrap($Left_Margin+270,$YPos,76,$FontSize,$DisplayPrice,'right');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos,36,$FontSize,$DisplayQty,'right');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+390,$YPos,26,$FontSize,$MyRow2['units'],'center');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+420,$YPos,26,$FontSize,$DisplayDiscount,'right');
				$LeftOvers = $pdf->addTextWrap($Page_Width-$Left_Margin-72, $YPos, 72, $FontSize, $DisplayNet, 'right');

				if($MyRow2['controlled']==1) {

					$GetControlMovts = DB_query("
						SELECT
							moveqty,
							serialno
						FROM stockserialmoves
						WHERE stockmoveno='" . $MyRow2['stkmoveno'] . "'");

					if($MyRow2['serialised']==1) {
						while($ControlledMovtRow = DB_fetch_array($GetControlMovts)) {
							$YPos -= (10*$Lines);
							$LeftOvers = $pdf->addTextWrap($Left_Margin+82,$YPos,100,$FontSize,$ControlledMovtRow['serialno'],'left');
							if($YPos-$LineHeight <= $Bottom_Margin) {
								/* head up a new invoice/credit note page */
								/*draw the vertical column lines right to the bottom */
								PrintLinesToBottom ();
								include('includes/PDFTransPageHeaderPortrait.php');
							} //end if need a new page headed up
						}
					} else {
						while($ControlledMovtRow = DB_fetch_array($GetControlMovts)) {
							$YPos -= (10*$Lines);
							$LeftOvers = $pdf->addTextWrap($Left_Margin+82,$YPos,100,$FontSize,(-$ControlledMovtRow['moveqty']) . ' x ' . $ControlledMovtRow['serialno'], 'left');
							if($YPos-$LineHeight <= $Bottom_Margin) {
								/* head up a new invoice/credit note page */
								/*draw the vertical column lines right to the bottom */
								PrintLinesToBottom ();
								include('includes/PDFTransPageHeaderPortrait.php');
							} //end if need a new page headed up
						}
					}
				}
				$YPos -= ($FontSize*$Lines);

				$Lines=explode("\r\n",htmlspecialchars_decode($MyRow2['narrative']));
				for ($i=0;$i<sizeOf($Lines);$i++) {
					while(mb_strlen($Lines[$i])>1) {
						if($YPos-$LineHeight <= $Bottom_Margin) {
							/* head up a new invoice/credit note page */
							/*draw the vertical column lines right to the bottom */
							PrintLinesToBottom ();
							include('includes/PDFTransPageHeaderPortrait.php');
						} //end if need a new page headed up
						/*increment a line down for the next line item */
						if(mb_strlen($Lines[$i])>1) {
							$Lines[$i] = $pdf->addTextWrap($Left_Margin+85,$YPos,181,$FontSize,stripslashes($Lines[$i]));
						}
						$YPos -= ($LineHeight);
					}
				}

				if($YPos <= $Bottom_Margin) {
					/* head up a new invoice/credit note page */
					/*draw the vertical column lines right to the bottom */
					PrintLinesToBottom ();
					include('includes/PDFTransPageHeaderPortrait.php');
				} //end if need a new page headed up

			} /*end while there are line items to print out*/

		} /*end if there are stock movements to show on the invoice or credit note*/

		$YPos -= $LineHeight;

		/* check to see enough space left to print the 4 lines for the totals/footer */
		if(($YPos-$Bottom_Margin)<(2*$LineHeight)) {
			PrintLinesToBottom ();
			include('includes/PDFTransPageHeaderPortrait.php');
		}
		/*Print a column vertical line with enough space for the footer*/
		/*draw the vertical column lines to 4 lines shy of the bottom
		to leave space for invoice footer info ie totals etc*/
		$pdf->line($Left_Margin+78, $TopOfColHeadings,$Left_Margin+78,$Bottom_Margin+(4*$LineHeight));

		/*Print a column vertical line */
		$pdf->line($Left_Margin+268, $TopOfColHeadings,$Left_Margin+268,$Bottom_Margin+(4*$LineHeight));

		/*Print a column vertical line */
		$pdf->line($Left_Margin+348, $TopOfColHeadings,$Left_Margin+348,$Bottom_Margin+(4*$LineHeight));

		/*Print a column vertical line */
		$pdf->line($Left_Margin+388, $TopOfColHeadings,$Left_Margin+388,$Bottom_Margin+(4*$LineHeight));

		/*Print a column vertical line */
		$pdf->line($Left_Margin+418, $TopOfColHeadings,$Left_Margin+418,$Bottom_Margin+(4*$LineHeight));

		$pdf->line($Left_Margin+448, $TopOfColHeadings,$Left_Margin+448,$Bottom_Margin+(4*$LineHeight));

		/*Rule off at bottom of the vertical lines */
		$pdf->line($Left_Margin, $Bottom_Margin+(4*$LineHeight),$Page_Width-$Right_Margin,$Bottom_Margin+(4*$LineHeight));

		/*Now print out the footer and totals */

		if($InvOrCredit=='Invoice') {
			$DisplaySubTot = locale_number_format($MyRow['ovamount'],$MyRow['decimalplaces']);
			$DisplayFreight = locale_number_format($MyRow['ovfreight'],$MyRow['decimalplaces']);
			$DisplayTax = locale_number_format($MyRow['ovgst'],$MyRow['decimalplaces']);
			$DisplayTotal = locale_number_format($MyRow['ovfreight']+$MyRow['ovgst']+$MyRow['ovamount'],$MyRow['decimalplaces']);
		} else {
			$DisplaySubTot = locale_number_format(-$MyRow['ovamount'],$MyRow['decimalplaces']);
			$DisplayFreight = locale_number_format(-$MyRow['ovfreight'],$MyRow['decimalplaces']);
			$DisplayTax = locale_number_format(-$MyRow['ovgst'],$MyRow['decimalplaces']);
			$DisplayTotal = locale_number_format(-$MyRow['ovfreight']-$MyRow['ovgst']-$MyRow['ovamount'],$MyRow['decimalplaces']);
		}

		$YPos = $Bottom_Margin+(3*$LineHeight);

	/*Print out the invoice text entered */
		$FontSize =8;
		$LeftOvers=explode("\r\n",DB_escape_string($MyRow['invtext']));
		for ($i=0;$i<sizeOf($LeftOvers);$i++) {
			$pdf->addTextWrap($Left_Margin, $YPos-8-($i*8), 290, $FontSize, $LeftOvers[$i]);
		}
		$FontSize = 10;

		$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-220, $YPos+5, 72, $FontSize, __('Sub Total'));
		$LeftOvers = $pdf->addTextWrap($Page_Width-$Left_Margin-72, $YPos+5, 72, $FontSize, $DisplaySubTot, 'right');

		$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-220, $YPos+5-$LineHeight, 72, $FontSize, __('Freight'));
		$LeftOvers = $pdf->addTextWrap($Page_Width-$Left_Margin-72, $YPos+5-$LineHeight, 72, $FontSize, $DisplayFreight, 'right');

		$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-220, $YPos+5-$LineHeight*2, 72, $FontSize, __('Tax'));
		$LeftOvers = $pdf->addTextWrap($Page_Width-$Left_Margin-72, $YPos+5-$LineHeight*2, 72, $FontSize, $DisplayTax, 'right');

		/*rule off for total */
		$pdf->line($Page_Width-$Right_Margin-222, $YPos-(2*$LineHeight),$Page_Width-$Right_Margin,$YPos-(2*$LineHeight));

		/*vertical to separate totals from comments and ROMALPA */
		$pdf->line($Page_Width-$Right_Margin-222, $YPos+$LineHeight,$Page_Width-$Right_Margin-222,$Bottom_Margin);

		$YPos+=10;
		if($InvOrCredit=='Invoice') {
			/* Print out the payment terms */
			$pdf->addTextWrap($Left_Margin, $YPos-5, 280, $FontSize,__('Payment Terms') . ': ' . $MyRow['terms']);

			$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-220, $Bottom_Margin+5, 144, $FontSize, __('TOTAL INVOICE'));

			$FontSize=8;
			$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos-18,290,$FontSize,$_SESSION['RomalpaClause']);
			while(mb_strlen($LeftOvers)>0 AND $YPos > $Bottom_Margin) {
				$YPos -=10;
				$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos-18,290,$FontSize,$LeftOvers);
			}

			/* Add Images for Visa / Mastercard / Paypal */
			if(file_exists('companies/' . $_SESSION['DatabaseName'] . '/payment.jpg')) {
				$pdf->addJpegFromFile('companies/' . $_SESSION['DatabaseName'] . '/payment.jpg',$Page_Width/2 -60,$YPos-15,0,20);
			}

			// Print Bank acount details if available and default for invoices is selected
			$pdf->addText($Left_Margin, $YPos+22-$LineHeight*3, $FontSize, $DefaultBankAccountCode . ' ' . $DefaultBankAccountNumber);
			$FontSize=10;
		} else {
			$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-220, $Bottom_Margin+5, 144, $FontSize, __('TOTAL CREDIT'));
 		}
		$LeftOvers = $pdf->addTextWrap($Page_Width-$Left_Margin-72, $Bottom_Margin+5, 72, $FontSize, $DisplayTotal, 'right');
		} /* end of check to see that there was an invoice record to print */

		$FromTransNo++;
	} /* end loop to print invoices */

	/* Put the transaction number back as would have been incremented by one after last pass */
	$FromTransNo--;

	if(isset($_GET['Email'])) { //email the invoice to address supplied
		$FileName = $_SESSION['reports_dir'] . '/' . $_SESSION['DatabaseName'] . '_' . $InvOrCredit . '_' . $_GET['FromTransNo'] . '.pdf';
		$pdf->Output($FileName,'F');

		$EmailSubject = $InvOrCredit . ' ' . $FromTransNo;
		$EmailBody = __('Please find attached') . ' ' . $InvOrCredit . ' ' . $FromTransNo;
		$EmailFrom = $_SESSION['CompanyRecord']['coyname'] . ' <' . $_SESSION['CompanyRecord']['email'] . '>';
		$EmailTo = $_GET['Email'];
		$Attachments = array(array($FileName, 'application/pdf'));

		$Result = SendEmailFromWebERP($EmailFrom, $EmailTo, $EmailSubject, $EmailBody, $Attachments);

		unlink($FileName); //delete the temporary file

		$Title = __('Emailing') . ' ' .$InvOrCredit . ' ' . __('Number') . ' ' . $FromTransNo;
		include('includes/header.php');
		// Adjust message based on SendEmailFromWebERP result
		if ($Result) {
			echo '<p>' . $InvOrCredit . ' ' . __('number') . ' ' . $FromTransNo . ' ' . __('has been emailed to') . ' ' . $_GET['Email'] . '</p>';
		} else {
			echo '<p>' . __('Failed to email') . ' ' . $InvOrCredit . ' ' . __('number') . ' ' . $FromTransNo . ' ' . __('to') . ' ' . $_GET['Email'] . '</p>';
		}
		include('includes/footer.php');
		exit();

	} else { //its not an email just print the invoice to PDF
		$pdf->OutputD($_SESSION['DatabaseName'] . '_' . $InvOrCredit . '_' . $FromTransNo . '.pdf');

	}
	$pdf->__destruct();

	// Change the language back to the user's language
	/// @todo use a better way to achieve that than setting a value into the session and including a file
	$_SESSION['Language'] = $UserLanguage;
	include($PathPrefix . 'includes/LanguageSetup.php');

} else { /*The option to print PDF was not hit */

	$Title=__('Select Invoices/Credit Notes To Print');
	include('includes/header.php');

	if(!isset($FromTransNo) OR $FromTransNo=='') {

	/*if FromTransNo is not set then show a form to allow input of either a single invoice number or a range of invoices to be printed. Also get the last invoice number created to show the user where the current range is up to */

		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . __('Print') . '" alt="" />' . ' ' . __('Print Invoices or Credit Notes (Portrait Mode)') . '</p>';

		echo '<fieldset>
				<legend>', __('Print Criteria'), '</legend>
				<field>
					<label for="InvOrCredit">' . __('Print Invoices or Credit Notes') . '</label>
					<select name="InvOrCredit">';

		if($InvOrCredit=='Invoice' OR !isset($InvOrCredit)) {
			echo '<option selected="selected" value="Invoice">' . __('Invoices') . '</option>';
			echo '<option value="Credit">' . __('Credit Notes') . '</option>';
		} else {
			echo '<option selected="selected" value="Credit">' . __('Credit Notes') . '</option>';
			echo '<option value="Invoice">' . __('Invoices') . '</option>';
		}
		echo '</select>
			</field>';

		echo '<field>
				<label for="PrintEDI">', __('Print EDI Transactions'), '</label>
				<select name="PrintEDI">';

		if($InvOrCredit=='Invoice' OR !isset($InvOrCredit)) {
			echo '<option selected="selected" value="No">' . __('Do not Print PDF EDI Transactions') . '</option>';
			echo '<option value="Yes">' . __('Print PDF EDI Transactions Too') . '</option>';
		} else {
			echo '<option value="No">' . __('Do not Print PDF EDI Transactions') . '</option>';
			echo '<option selected="selected" value="Yes">' . __('Print PDF EDI Transactions Too') . '</option>';
		}

		echo '</select>
			</field>';
		echo '<field>
				<label for="FromTransNo">' . __('Start invoice/credit note number to print') . '</label>
				<input class="number" type="text" maxlength="6" size="7" name="FromTransNo" required="required" />
			</field>';
		echo '<field>
				<label for="ToTransNo">' . __('End invoice/credit note number to print') . '</label>
				<input class="number" type="text" maxlength="6" size="7" name="ToTransNo" />
			</field>
			</fieldset>';
		echo '<div class="centre">
				<input type="submit" name="Print" value="' . __('Print Preview') . '" />
				<input type="submit" name="PrintPDF" value="' . __('Print PDF') . '" />
			</div>';

		$SQL = "SELECT typeno FROM systypes WHERE typeid=10";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);

		echo '<div class="page_help_text"><b>' . __('The last invoice created was number') . ' ' . $MyRow[0] . '</b><br />' . __('If only a single invoice is required') . ', ' . __('enter the invoice number to print in the Start transaction number to print field and leave the End transaction number to print field blank') . '. ' . __('Only use the end invoice to print field if you wish to print a sequential range of invoices') . '';

		$SQL = "SELECT typeno FROM systypes WHERE typeid=11";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);

		echo '<br /><b>' . __('The last credit note created was number') . ' ' . $MyRow[0] . '</b><br />' . __('A sequential range can be printed using the same method as for invoices above') . '. ' . __('A single credit note can be printed by only entering a start transaction number') . '</div>';
		echo '</div>
			</form>';

	} else { // A FromTransNo number IS set

		while($FromTransNo <= $_POST['ToTransNo']) {

	/*retrieve the invoice details from the database to print
	notice that salesorder record must be present to print the invoice purging of sales orders will
	nobble the invoice reprints */

			if($InvOrCredit=='Invoice') {

				$SQL = "SELECT debtortrans.trandate,
							debtortrans.ovamount,
							debtortrans.ovdiscount,
							debtortrans.ovfreight,
							debtortrans.ovgst,
							debtortrans.rate,
							debtortrans.invtext,
							debtortrans.consignment,
							debtorsmaster.name,
							debtorsmaster.address1,
							debtorsmaster.address2,
							debtorsmaster.address3,
							debtorsmaster.address4,
							debtorsmaster.address5,
							debtorsmaster.address6,
							debtorsmaster.currcode,
							salesorders.deliverto,
							salesorders.deladd1,
							salesorders.deladd2,
							salesorders.deladd3,
							salesorders.deladd4,
							salesorders.deladd5,
							salesorders.deladd6,
							salesorders.customerref,
							salesorders.orderno,
							salesorders.orddate,
							shippers.shippername,
							custbranch.brname,
							custbranch.braddress1,
							custbranch.braddress2,
							custbranch.braddress3,
							custbranch.braddress4,
							custbranch.braddress5,
							custbranch.braddress6,
							custbranch.salesman,
							salesman.salesmanname,
							debtortrans.debtorno,
							currencies.decimalplaces,
							paymentterms.dayinfollowingmonth,
							paymentterms.daysbeforedue,
							paymentterms.terms
						FROM debtortrans INNER JOIN debtorsmaster
						ON debtortrans.debtorno=debtorsmaster.debtorno
						INNER JOIN custbranch
						ON debtortrans.debtorno=custbranch.debtorno
						AND debtortrans.branchcode=custbranch.branchcode
						INNER JOIN salesorders
						ON debtortrans.order_ = salesorders.orderno
						INNER JOIN shippers
						ON debtortrans.shipvia=shippers.shipper_id
						INNER JOIN salesman
						ON custbranch.salesman=salesman.salesmancode
						INNER JOIN locations
						ON salesorders.fromstkloc=locations.loccode
						INNER JOIN locationusers
						ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1
						INNER JOIN paymentterms
						ON debtorsmaster.paymentterms=paymentterms.termsindicator
						INNER JOIN currencies
						ON debtorsmaster.currcode=currencies.currabrev
						WHERE debtortrans.type=10
						AND debtortrans.transno='" . $FromTransNo . "'";
			} else { //its a credit note

				$SQL = "SELECT debtortrans.trandate,
							debtortrans.ovamount,
							debtortrans.ovdiscount,
							debtortrans.ovfreight,
							debtortrans.ovgst,
							debtortrans.rate,
							debtortrans.invtext,
							debtorsmaster.name,
							debtorsmaster.address1,
							debtorsmaster.address2,
							debtorsmaster.address3,
							debtorsmaster.address4,
							debtorsmaster.address5,
							debtorsmaster.address6,
							debtorsmaster.currcode,
							custbranch.brname,
							custbranch.braddress1,
							custbranch.braddress2,
							custbranch.braddress3,
							custbranch.braddress4,
							custbranch.braddress5,
							custbranch.braddress6,
							custbranch.salesman,
							salesman.salesmanname,
							debtortrans.debtorno,
							currencies.decimalplaces
						FROM debtortrans INNER JOIN debtorsmaster
						ON debtortrans.debtorno=debtorsmaster.debtorno
						INNER JOIN custbranch
						ON debtortrans.debtorno=custbranch.debtorno
						AND debtortrans.branchcode=custbranch.branchcode
						INNER JOIN salesman
						ON custbranch.salesman=salesman.salesmancode
						INNER JOIN currencies
						ON debtorsmaster.currcode=currencies.currabrev
						WHERE debtortrans.type=11
						AND debtortrans.transno='" . $FromTransNo . "'";

			}

			$ErrMsg = __('There was a problem retrieving the invoice or credit note details for note number') . ' ' . $InvoiceToPrint;
			$Result = DB_query($SQL, $ErrMsg);
			if(DB_num_rows($Result)==0) {
				echo '<p>' . $ErrMsg . ' ' . __('from the database') . '. ' . __('To print an invoice, the sales order record, the customer transaction record and the branch record for the customer must not have been purged') . '. ' . __('To print a credit note only requires the customer, transaction, salesman and branch records be available');
				break;
				include('includes/footer.php');
				exit();
			} elseif(DB_num_rows($Result)==1) {
	/* Then there's an invoice (or credit note) to print. So print out the invoice header and GST Number from the company record */

				$MyRow = DB_fetch_array($Result);

				if ($_SESSION['SalesmanLogin']!='' AND $_SESSION['SalesmanLogin']!=$MyRow['salesman']) {
					prnMsg(__('Your account is set up to see only a specific salespersons orders. You are not authorised to view transaction for this order'),'error');
					include('includes/footer.php');
					exit();
				}
				if( $CustomerLogin == 1 AND $MyRow['debtorno'] != $_SESSION['CustomerID']) {
					echo '<p class="bad">' . __('This transaction is addressed to another customer and cannot be displayed for privacy reasons') . '. ' . __('Please select only transactions relevant to your company');
					include('includes/footer.php');
					exit();
				}

				$ExchRate = $MyRow['rate'];
				$PageNumber = 1;

				echo '<div class="sheet">
					<table class="table1">
						<tr>
							<td valign="top" style="width:10%"><img src="' . $_SESSION['LogoFile'] . '" alt="" /></td>
							<td style="background-color:#bbbbbb">';

				if($InvOrCredit=='Invoice') {
					echo '<h2>' . __('TAX INVOICE') . ' ';
				} else {
					echo '<h2 style="color:red">' . __('TAX CREDIT NOTE') . ' ';
				}
				echo __('Number') . ' ' . $FromTransNo . '</h2>
						<br />' . __('Tax Authority Ref') . '. ' . $_SESSION['CompanyRecord']['gstno'];

				if ( $InvOrCredit == 'Invoice' ) {
					/* Print payment terms and due date */
					$DisplayDueDate = CalcDueDate(ConvertSQLDate($MyRow['trandate']), $MyRow['dayinfollowingmonth'], $MyRow['daysbeforedue']);
					echo '<br />' . __('Payment Terms') . ': '. $MyRow['terms'] . '<br />' . __('Due Date') . ': ' . $DisplayDueDate;
				}

				echo '</td>
					</tr>
					</table>';

	/* Main table with customer name and charge to info. */
				echo '<table class="table1">
						<tr>
							<td><h2>' . $_SESSION['CompanyRecord']['coyname'] . '</h2>
							<br />';
				echo $_SESSION['CompanyRecord']['regoffice1'] . '<br />';
				echo $_SESSION['CompanyRecord']['regoffice2'] . '<br />';
				echo $_SESSION['CompanyRecord']['regoffice3'] . '<br />';
				echo $_SESSION['CompanyRecord']['regoffice4'] . '<br />';
				echo $_SESSION['CompanyRecord']['regoffice5'] . '<br />';
				echo $_SESSION['CompanyRecord']['regoffice6'] . '<br />';
				echo __('Telephone') . ': ' . $_SESSION['CompanyRecord']['telephone'] . '<br />';
				echo __('Facsimile') . ': ' . $_SESSION['CompanyRecord']['fax'] . '<br />';
				echo __('Email') . ': ' . $_SESSION['CompanyRecord']['email'] . '<br />';

				echo '</td>
					<td style="width:50%" class="number">';

	/*Now the customer charged to details in a sub table within a cell of the main table*/

				echo '<table class="table1">
						<tr>
							<td align="left" style="background-color:#bbbbbb"><b>' . __('Charge To') . ':</b></td>
						</tr>
						<tr>
							<td style="background-color:#eeeeee">';
				echo $MyRow['name'] .
					'<br />' . $MyRow['address1'] .
					'<br />' . $MyRow['address2'] .
					'<br />' . $MyRow['address3'] .
					'<br />' . $MyRow['address4'] .
					'<br />' . $MyRow['address5'] .
					'<br />' . $MyRow['address6'];

				echo '</td>
					</tr>
					</table>';
				/*end of the small table showing charge to account details */
				echo __('Page') . ': ' . $PageNumber;
				echo '</td>
					</tr>
					</table>';
				/*end of the main table showing the company name and charge to details */

				if($InvOrCredit=='Invoice') {
	/* Table with Charge Branch and Delivered To info. */
					echo '<table class="table1">
						<tr>
							<td align="left" style="background-color:#bbbbbb"><b>' . __('Charge Branch') . ':</b></td>
							<td align="left" style="background-color:#bbbbbb"><b>' . __('Delivered To') . ':</b></td>
						</tr>';
					echo '<tr>
						<td style="background-color:#eeeeee">' .$MyRow['brname'] .
									'<br />' . $MyRow['braddress1'] .
									'<br />' . $MyRow['braddress2'] .
									'<br />' . $MyRow['braddress3'] .
									'<br />' . $MyRow['braddress4'] .
									'<br />' . $MyRow['braddress5'] .
									'<br />' . $MyRow['braddress6'] .
						'</td>';

					echo '<td style="background-color:#eeeeee">' . $MyRow['deliverto'] .
									'<br />' . $MyRow['deladd1'] .
									'<br />' . $MyRow['deladd2'] .
									'<br />' . $MyRow['deladd3'] .
									'<br />' . $MyRow['deladd4'] .
									'<br />' . $MyRow['deladd5'] .
									'<br />' . $MyRow['deladd6'] .
						'</td>';
					echo '</tr>
					</table><hr />';
	/* End Charge Branch and Delivered To table */
	/* Table with order details */
					echo '<table class="table1">
						<tr>
							<td align="left" style="background-color:#bbbbbb"><b>' . __('Your Order Ref') . '</b></td>
							<td align="left" style="background-color:#bbbbbb"><b>' . __('Our Order No') . '</b></td>
							<td align="left" style="background-color:#bbbbbb"><b>' . __('Order Date') . '</b></td>
							<td align="left" style="background-color:#bbbbbb"><b>' . __('Invoice Date') . '</b></td>
							<td align="left" style="background-color:#bbbbbb"><b>' . __('Sales Person') . '</b></td>
							<td align="left" style="background-color:#bbbbbb"><b>' . __('Shipper') . '</b></td>
							<td align="left" style="background-color:#bbbbbb"><b>' . __('Consignment Ref') . '</b></td>
						</tr>';
					echo '<tr>
							<td style="background-color:#EEEEEE">' . $MyRow['customerref'] . '</td>
							<td style="background-color:#EEEEEE">' .$MyRow['orderno'] . '</td>
							<td style="background-color:#EEEEEE">' . ConvertSQLDate($MyRow['orddate']) . '</td>
							<td style="background-color:#EEEEEE">' . ConvertSQLDate($MyRow['trandate']) . '</td>
							<td style="background-color:#EEEEEE">' . $MyRow['salesmanname'] . '</td>
							<td style="background-color:#EEEEEE">' . $MyRow['shippername'] . '</td>
							<td style="background-color:#EEEEEE">' . $MyRow['consignment'] . '</td>
						</tr>
					</table>';
	/* End order details table */
					$SQL ="SELECT stockmoves.stockid,
								stockmaster.description,
								-stockmoves.qty as quantity,
								stockmoves.discountpercent,
								((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . "* -stockmoves.qty) AS fxnet,
								(stockmoves.price * " . $ExchRate . ") AS fxprice,
								stockmoves.narrative,
								stockmaster.units,
								stockmaster.decimalplaces
							FROM stockmoves
							INNER JOIN stockmaster
							ON stockmoves.stockid = stockmaster.stockid
							WHERE stockmoves.type=10
							AND stockmoves.transno='" . $FromTransNo . "'
							AND stockmoves.show_on_inv_crds=1";

				} else { /* then its a credit note */
	/* Table for Branch info */
					echo '<table width="50%">
						<tr>
							<td align="left" style="background-color:#BBBBBB"><b>' . __('Branch') . ':</b></td>
						</tr>';
					echo '<tr>
							<td style="background-color:#EEEEEE">' . $MyRow['brname'] .
										'<br />' . $MyRow['braddress1'] .
										'<br />' . $MyRow['braddress2'] .
										'<br />' . $MyRow['braddress3'] .
										'<br />' . $MyRow['braddress4'] .
										'<br />' . $MyRow['braddress5'] .
										'<br />' . $MyRow['braddress6'] .
								'</td>
					</tr></table>';
	/* End Branch info table */
	/* Table for Sales Person info. */
					echo '<hr />
						<table class="table1">
						<tr>
							<td align="left" style="background-color:#bbbbbb"><b>' . __('Date') . '</b></td>
							<td align="left" style="background-color:#BBBBBB"><b>' . __('Sales Person') . '</b></td>
						</tr>';
					echo '<tr>
							<td style="background-color:#EEEEEE">' . ConvertSQLDate($MyRow['trandate']) . '</td>
							<td style="background-color:#EEEEEE">' . $MyRow['salesmanname'] . '</td>
						</tr>
						</table>';
	/* End Sales Person table */
					$SQL ="SELECT stockmoves.stockid,
								stockmaster.description,
								stockmoves.qty as quantity,
								stockmoves.discountpercent, ((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . " * stockmoves.qty) AS fxnet,
								(stockmoves.price * " . $ExchRate . ") AS fxprice,
								stockmaster.units,
								stockmoves.narrative,
								stockmaster.decimalplaces
							FROM stockmoves
							INNER JOIN stockmaster
							ON stockmoves.stockid = stockmaster.stockid
							WHERE stockmoves.type=11
							AND stockmoves.transno='" . $FromTransNo . "'
							AND stockmoves.show_on_inv_crds=1";
				}

				echo '<hr />';
				echo '<div class="centre"><h4>' . __('All amounts stated in') . ' ' . $MyRow['currcode'] . '</h4></div>';

				$ErrMsg = __('There was a problem retrieving the invoice or credit note stock movement details for invoice number') . ' ' . $FromTransNo ;
				$Result = DB_query($SQL, $ErrMsg);

				if(DB_num_rows($Result)>0) {
	/* Table for stock details */
					echo '<table class="table1">
							<tr>
								<th>' . __('Item Code') . '</th>
								<th>' . __('Item Description') . '</th>
								<th>' . __('Quantity') . '</th>
								<th>' . __('Unit') . '</th>
								<th>' . __('Price') . '</th>
								<th>' . __('Discount') . '</th>
								<th>' . __('Net') . '</th>
							</tr>';

					$LineCounter =17;

					while($MyRow2=DB_fetch_array($Result)) {

						$DisplayPrice =locale_number_format($MyRow2['fxprice'],$MyRow['decimalplaces']);
						$DisplayQty = locale_number_format($MyRow2['quantity'],$MyRow2['decimalplaces']);
						$DisplayNet = locale_number_format($MyRow2['fxnet'],$MyRow['decimalplaces']);

						if($MyRow2['discountpercent']==0) {
							$DisplayDiscount ='';
						} else {
							$DisplayDiscount = locale_number_format($MyRow2['discountpercent']*100,2) . '%';
						}

						printf ('<tr class="striped_row">
								<td>%s</td>
								<td>%s</td>
								<td class="number">%s</td>
								<td class="number">%s</td>
								<td class="number">%s</td>
								<td class="number">%s</td>
								<td class="number">%s</td>
								</tr>',
								$MyRow2['stockid'],
								$MyRow2['description'],
								$DisplayQty,
								$MyRow2['units'],
								$DisplayPrice,
								$DisplayDiscount,
								$DisplayNet);

						if(mb_strlen($MyRow2['narrative'])>1) {
							echo '<tr class="striped_row">
								<td></td>
								<td colspan="6">' . $MyRow2['narrative'] . '</td>
								</tr>';
							$LineCounter++;
						}

						$LineCounter++;

						if($LineCounter == ($_SESSION['PageLength'] - 2)) {

							/* head up a new invoice/credit note page */

							$PageNumber++;
	/* End the stock table before the new page */
							echo '</table>
								<table class="table1">
								<tr>
									<td valign="top"><img src="' . $_SESSION['LogoFile'] . '" alt="" /></td>
									<td style="background-color:#bbbbbb">';

							if($InvOrCredit=='Invoice') {
								echo '<h2>' . __('TAX INVOICE') . ' ';
							} else {
								echo '<h2 style="color:red">' . __('TAX CREDIT NOTE') . ' ';
							}
							echo __('Number') . ' ' . $FromTransNo . '</h2><br />' . __('GST Number') . ' - ' . $_SESSION['CompanyRecord']['gstno'] . '</td>
							</tr>
							</table>';

	/*Now print out company name and address */
							echo '<table class="table1">
									<tr>
										<td><h2>' . $_SESSION['CompanyRecord']['coyname'] . '</h2><br />';
							echo $_SESSION['CompanyRecord']['regoffice1'] . '<br />';
							echo $_SESSION['CompanyRecord']['regoffice2'] . '<br />';
							echo $_SESSION['CompanyRecord']['regoffice3'] . '<br />';
							echo $_SESSION['CompanyRecord']['regoffice4'] . '<br />';
							echo $_SESSION['CompanyRecord']['regoffice5'] . '<br />';
							echo $_SESSION['CompanyRecord']['regoffice6'] . '<br />';
							echo __('Telephone') . ': ' . $_SESSION['CompanyRecord']['telephone'] . '<br />';
							echo __('Facsimile') . ': ' . $_SESSION['CompanyRecord']['fax'] . '<br />';
							echo __('Email') . ': ' . $_SESSION['CompanyRecord']['email'] . '<br />';
							echo '</td><td class="number">' . __('Page') . ': ' . $PageNumber . '</td>
									</tr>
								</table>';
							echo '<table class="table1">
									<tr>
										<th>' . __('Item Code') . '</th>
										<th>' . __('Item Description') . '</th>
										<th>' . __('Quantity') . '</th>
										<th>' . __('Unit') . '</th>
										<th>' . __('Price') . '</th>
										<th>' . __('Discount') . '</th>
										<th>' . __('Net') . '</th>
									</tr>';

							$LineCounter = 10;

						} //end if need a new page headed up
					} //end while there are line items to print out
					echo '</table>';
				} /*end if there are stock movements to show on the invoice or credit note*/

				/* check to see enough space left to print the totals/footer */
				$LinesRequiredForText = floor(mb_strlen($MyRow['invtext'])/140);

				if($LineCounter >= ($_SESSION['PageLength'] - 8 - $LinesRequiredForText)) {

					/* head up a new invoice/credit note page */

					$PageNumber++;
					echo '<table class="table1">
							<tr>
								<td valign="top"><img src="' . $_SESSION['LogoFile'] . '" alt="" /></td>
								<td style="background-color:#bbbbbb">';
					if($InvOrCredit=='Invoice') {
						echo '<h2>' . __('TAX INVOICE') .' ';
					} else {
						echo '<h2 style="color:red">' . __('TAX CREDIT NOTE') . ' ';
					}
					echo __('Number') . ' ' . $FromTransNo . '</h2>
							<br />' . __('GST Number') . ' - ' . $_SESSION['CompanyRecord']['gstno'] . '</td>
						</tr>
						</table>';

	/*Print the company name and address */
					echo '<table class="table1">
							<tr>
								<td><h2>' . $_SESSION['CompanyRecord']['coyname'] . '</h2><br />';
					echo $_SESSION['CompanyRecord']['regoffice1'] . '<br />';
					echo $_SESSION['CompanyRecord']['regoffice2'] . '<br />';
					echo $_SESSION['CompanyRecord']['regoffice3'] . '<br />';
					echo $_SESSION['CompanyRecord']['regoffice4'] . '<br />';
					echo $_SESSION['CompanyRecord']['regoffice5'] . '<br />';
					echo $_SESSION['CompanyRecord']['regoffice6'] . '<br />';
					echo __('Telephone') . ': ' . $_SESSION['CompanyRecord']['telephone'] . '<br />';
					echo __('Facsimile') . ': ' . $_SESSION['CompanyRecord']['fax'] . '<br />';
					echo __('Email') . ': ' . $_SESSION['CompanyRecord']['email'] . '<br />';
					echo '</td><td class="number">' . __('Page') . ': ' . $PageNumber . '</td>
						</tr>
						</table>';
					echo '<table class="table1">
							<tr>
								<th>' . __('Item Code') . '</th>
								<th>' . __('Item Description') . '</th>
								<th>' . __('Quantity') . '</th>
								<th>' . __('Unit') . '</th>
								<th>' . __('Price') . '</th>
								<th>' . __('Discount') . '</th>
								<th>' . __('Net') . '</th>
							</tr>
						</table>';

					$LineCounter = 10;
				}

	/*Print out the invoice text entered */
				echo '<br /><br />' . $MyRow['invtext'];

	/*Space out the footer to the bottom of the page */
				$LineCounter=$LineCounter+2+$LinesRequiredForText;
				while($LineCounter < ($_SESSION['PageLength'] -6)) {
					echo '<br />';
					$LineCounter++;
				}

	/*Now print out the footer and totals */

				if($InvOrCredit=='Invoice') {
					$DisplaySubTot = locale_number_format($MyRow['ovamount'],$MyRow['decimalplaces']);
					$DisplayFreight = locale_number_format($MyRow['ovfreight'],$MyRow['decimalplaces']);
					$DisplayTax = locale_number_format($MyRow['ovgst'],$MyRow['decimalplaces']);
					$DisplayTotal = locale_number_format($MyRow['ovfreight']+$MyRow['ovgst']+$MyRow['ovamount'],$MyRow['decimalplaces']);
				} else {
					$DisplaySubTot = locale_number_format(-$MyRow['ovamount'],$MyRow['decimalplaces']);
					$DisplayFreight = locale_number_format(-$MyRow['ovfreight'],$MyRow['decimalplaces']);
					$DisplayTax = locale_number_format(-$MyRow['ovgst'],$MyRow['decimalplaces']);
					$DisplayTotal = locale_number_format(-$MyRow['ovfreight']-$MyRow['ovgst']-$MyRow['ovamount'],$MyRow['decimalplaces']);
				}

				echo '<table class="table1"><tr>
					<td class="number">' . __('Sub Total') . '</td>
					<td class="number" style="background-color:#EEEEEE;width:15%">' . $DisplaySubTot . '</td></tr>';
				echo '<tr><td class="number">' . __('Freight') . '</td>
					<td class="number" style="background-color:#EEEEEE">' . $DisplayFreight . '</td></tr>';
				echo '<tr><td class="number">' . __('Tax') . '</td>
					<td class="number" style="background-color:#EEEEEE">' . $DisplayTax . '</td></tr>';
				if($InvOrCredit=='Invoice') {
					echo '<tr><td class="number"><b>' . __('TOTAL INVOICE') . '</b></td>
						<td class="number" style="background-color:#EEEEEE"><b>' . $DisplayTotal . '</b></td></tr>';
				} else {
					echo '<tr><td class="number" style="color:red"><b>' . __('TOTAL CREDIT') . '</b></td>
							<td class="number" style="background-color:#EEEEEE;color:red"><b>' . $DisplayTotal . '</b></td></tr>';
				}
				echo '</table>
					</div>';
	/* End footer totals table */
			} /* end of check to see that there was an invoice record to print */
			$FromTransNo++;
		} /* end loop to print invoices */
	} /*end of if FromTransNo exists */
	include('includes/footer.php');

} /*end of else not PrintPDF */


function PrintLinesToBottom () {

	global $Bottom_Margin;
	global $Left_Margin;
	global $LineHeight;
	global $PageNumber;
	global $pdf;
	global $TopOfColHeadings;

	// Prints column vertical lines:
	$pdf->line($Left_Margin+ 78, $TopOfColHeadings,$Left_Margin+ 78,$Bottom_Margin);
	$pdf->line($Left_Margin+268, $TopOfColHeadings,$Left_Margin+268,$Bottom_Margin);
	$pdf->line($Left_Margin+348, $TopOfColHeadings,$Left_Margin+348,$Bottom_Margin);
	$pdf->line($Left_Margin+388, $TopOfColHeadings,$Left_Margin+388,$Bottom_Margin);
	$pdf->line($Left_Margin+418, $TopOfColHeadings,$Left_Margin+418,$Bottom_Margin);
	$pdf->line($Left_Margin+448, $TopOfColHeadings,$Left_Margin+448,$Bottom_Margin);

	$PageNumber++;
}

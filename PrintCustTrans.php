<?php
/* $Id$ */
$PageSecurity = 1;
include ('includes/session.inc');
if (isset($_GET['FromTransNo'])) {
	$FromTransNo = trim($_GET['FromTransNo']);
} elseif (isset($_POST['FromTransNo'])) {
	$FromTransNo = trim($_POST['FromTransNo']);
} else {
	$FromTransNo = '';
}
if (isset($_GET['InvOrCredit'])) {
	$InvOrCredit = $_GET['InvOrCredit'];
} elseif (isset($_POST['InvOrCredit'])) {
	$InvOrCredit = $_POST['InvOrCredit'];
}
if (isset($_GET['PrintPDF'])) {
	$PrintPDF = TRUE;
} elseif (isset($_POST['PrintPDF'])) {
	$PrintPDF = TRUE;
}
if (!isset($_POST['ToTransNo']) or trim($_POST['ToTransNo']) == '' or $_POST['ToTransNo'] < $FromTransNo) {
	$_POST['ToTransNo'] = $FromTransNo;
}
$FirstTrans = $FromTransNo; /* Need to start a new page only on subsequent transactions */
if ($FromTransNo=='Preview') {
	$FormDesign = simplexml_load_file(sys_get_temp_dir().'/SalesInvoice.xml');
} else {
	$FormDesign = simplexml_load_file($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/SalesInvoice.xml');
}
if (isset($PrintPDF) or isset($_GET['PrintPDF']) and $PrintPDF and isset($FromTransNo) and isset($InvOrCredit) and $FromTransNo != '') {
	$PaperSize = $FormDesign->PaperSize;
	include ('includes/PDFStarter.php');
	require ('fpdi/fpdi.php');
	// Javier: now I use the native constructor, better to not use references
	if ($InvOrCredit == 'Invoice') {
		$pdf->addInfo('Title', _('Sales Invoice') . ' ' . $FromTransNo . ' to ' . $_POST['ToTransNo']);
		$pdf->addInfo('Subject', _('Invoices from') . ' ' . $FromTransNo . ' ' . _('to') . ' ' . $_POST['ToTransNo']);
	} else {
		$pdf->addInfo('Title', _('Sales Credit Note'));
		$pdf->addInfo('Subject', _('Credit Notes from') . ' ' . $FromTransNo . ' ' . _('to') . ' ' . $_POST['ToTransNo']);
	}
	/* Javier: I have brought this piece from the pdf class constructor to get it closer to the admin/user,
	I corrected it to match TCPDF, but it still needs some check, after which,
	I think it should be moved to each report to provide flexible Document Header and Margins in a per-report basis. */
	/* END Brought from class.pdf.php constructor */
	//	$pdf->selectFont('helvetica');
	$FirstPage = true;
	$line_height = $FormDesign->LineHeight;
	while ($FromTransNo <= $_POST['ToTransNo']) {
		/* retrieve the invoice details from the database to print
		notice that salesorder record must be present to print the invoice purging of sales orders will
		nobble the invoice reprints */
		if ($InvOrCredit == 'Invoice') {
			$sql = "SELECT debtortrans.trandate,
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
					debtorsmaster.invaddrbranch,
					debtorsmaster.taxref,
					paymentterms.terms,
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
					salesman.salesmanname,
					debtortrans.debtorno,
					debtortrans.branchcode
				FROM debtortrans,
					debtorsmaster,
					custbranch,
					salesorders,
					shippers,
					salesman,
					locations,
					paymentterms
				WHERE debtortrans.order_ = salesorders.orderno
				AND debtortrans.type=10
				AND debtortrans.transno='" . $FromTransNo . "'
				AND debtortrans.shipvia=shippers.shipper_id
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtorsmaster.paymentterms=paymentterms.termsindicator
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
				AND custbranch.salesman=salesman.salesmancode
				AND salesorders.fromstkloc=locations.loccode";
			if (isset($_POST['PrintEDI']) and $_POST['PrintEDI'] == 'No') {
				$sql = $sql . " AND debtorsmaster.ediinvoices=0";
			}
		} else {
			$sql = "SELECT debtortrans.trandate,
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
					salesman.salesmanname,
					debtortrans.debtorno,
					debtortrans.branchcode,
					paymentterms.terms
				FROM debtortrans,
					debtorsmaster,
					custbranch,
					salesman,
					paymentterms
				WHERE debtortrans.type=11
				AND debtorsmaster.paymentterms = paymentterms.termsindicator
				AND debtortrans.transno='" . $FromTransNo . "'
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
				AND custbranch.salesman=salesman.salesmancode";
			if ($_POST['PrintEDI'] == 'No') {
				$sql = $sql . ' AND debtorsmaster.ediinvoices=0';
			}
		} // end else
		if ($FromTransNo!='Preview') {
			$result = DB_query($sql, $db, '', '', false, false);
			if (DB_error_no($db) != 0) {
				$title = _('Transaction Print Error Report');
				include ('includes/header.inc');
				prnMsg(_('There was a problem retrieving the invoice or credit note details for note number') . ' ' . $InvoiceToPrint . ' ' . _('from the database') . '. ' . _('To print an invoice, the sales order record, the customer transaction record and the branch record for the customer must not have been purged') . '. ' . _('To print a credit note only requires the customer, transaction, salesman and branch records be available'), 'error');
				if ($debug == 1) {
					prnMsg(_('The SQL used to get this information that failed was') . "<br />" . $sql, 'error');
				}
				include ('includes/footer.inc');
				exit;
			}
		}
		if ($FromTransNo=='Preview' or DB_num_rows($result) == 1) {
			if ($FromTransNo!='Preview') {
				$myrow = DB_fetch_array($result);
				$ExchRate = $myrow['rate'];
			} else {
				$ExchRate = 'X';
			}
			if ($InvOrCredit == 'Invoice') {
				$sql = "SELECT stockmoves.stockid,
						stockmaster.description,
						-stockmoves.qty as quantity,
						stockmoves.discountpercent,
						((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . "* -stockmoves.qty) AS fxnet,
						(stockmoves.price * " . $ExchRate . ") AS fxprice,
						stockmoves.narrative,
						stockmaster.units,
						stockmaster.decimalplaces
					FROM stockmoves,
						stockmaster
					WHERE stockmoves.stockid = stockmaster.stockid
					AND stockmoves.type=10
					AND stockmoves.transno='" . $FromTransNo . "'
					AND stockmoves.show_on_inv_crds=1";
			} else {
				/* only credit notes to be retrieved */
				$sql = "SELECT stockmoves.stockid,
						stockmaster.description,
						stockmoves.qty as quantity,
						stockmoves.discountpercent,
						((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . " * stockmoves.qty) AS fxnet,
						(stockmoves.price * " . $ExchRate . ") AS fxprice,
						stockmoves.narrative,
						stockmaster.units,
						stockmaster.decimalplaces
					FROM stockmoves,
						stockmaster
					WHERE stockmoves.stockid = stockmaster.stockid
					AND stockmoves.type=11
					AND stockmoves.transno='" . $FromTransNo . "'
					AND stockmoves.show_on_inv_crds=1";
			} // end else
			if ($FromTransNo!='Preview') {
				$result = DB_query($sql, $db);
			}
			if (DB_error_no($db) != 0) {
				$title = _('Transaction Print Error Report');
				include ('includes/header.inc');
				echo '<br>' . _('There was a problem retrieving the invoice or credit note stock movement details for invoice number') . ' ' . $FromTransNo . ' ' . _('from the database');
				if ($debug == 1) {
					echo '<br>' . _('The SQL used to get this information that failed was') . "<br>$sql";
				}
				include ('includes/footer.inc');
				exit;
			}
			if ($FromTransNo=='Preview' or DB_num_rows($result) > 0) {
				$PageNumber = 1;
				include ('includes/PDFTransPageHeader.inc');
				$FirstPage = False;
				$YPos = $Page_Height - $FormDesign->Data->y;
				$Line=1;
				while (($FromTransNo=='Preview' and $Line==1) or (isset($result) and $myrow2 = DB_fetch_array($result))) {
					if ($myrow2['discountpercent'] == 0) {
						$DisplayDiscount = '';
					} else {
						$DisplayDiscount = number_format($myrow2['discountpercent'] * 100, 2) . '%';
						$DiscountPrice = $myrow2['fxprice'] * (1 - $myrow2['discountpercent']);
					}
					$DisplayNet = number_format($myrow2['fxnet'], 2);
					$DisplayPrice = $myrow2['fxprice'];
					$DisplayQty = $myrow2['quantity'];
					$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column1->x, $YPos,$FormDesign->Data->Column1->Length, $FormDesign->Data->Column1->FontSize, $myrow2['stockid']);
					$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column2->x, $YPos,$FormDesign->Data->Column2->Length, $FormDesign->Data->Column2->FontSize, $myrow2['description']);
					$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column3->x, $YPos,$FormDesign->Data->Column3->Length, $FormDesign->Data->Column3->FontSize, number_format($DisplayPrice,4), 'right');
					$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column4->x, $YPos,$FormDesign->Data->Column4->Length, $FormDesign->Data->Column4->FontSize, number_format($DisplayQty,$myrow2['decimalplaces']), 'right');
					$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column5->x, $YPos,$FormDesign->Data->Column5->Length, $FormDesign->Data->Column5->FontSize, $myrow2['units'], 'centre');
					$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column6->x, $YPos,$FormDesign->Data->Column6->Length, $FormDesign->Data->Column6->FontSize, $DisplayDiscount, 'right');
					$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column7->x, $YPos,$FormDesign->Data->Column7->Length, $FormDesign->Data->Column7->FontSize, $DisplayNet, 'right');
					$YPos-= ($line_height);
					$lines = explode('\r\n', $myrow2['narrative']);
					for ($i = 0;$i < sizeOf($lines);$i++) {
						while (strlen($lines[$i]) > 1) {
							if ($YPos - $line_height <= $Bottom_Margin) {
								/* head up a new invoice/credit note page */
								/* draw the vertical column lines right to the bottom */
								PrintLinesToBottom($pdf, $Page_Height, $PageNumber, $FormDesign);
								include ('includes/PDFTransPageHeaderPortrait.inc');
							} //end if need a new page headed up
							/* increment a line down for the next line item */
							if (strlen($lines[$i]) > 1) {
								$lines[$i] = $pdf->addTextWrap($Left_Margin + 100, $YPos, 245, $FontSize, stripslashes($lines[$i]));
							}
							$YPos-= ($line_height);
						}
					}
					if ($YPos <= $Bottom_Margin) {
						/* head up a new invoice/credit note page */
						/*draw the vertical column lines right to the bottom */
						PrintLinesToBottom($pdf, $Page_Height, $PageNumber, $FormDesign);
						include ('includes/PDFTransPageHeader.inc');
					} //end if need a new page headed up
					$Line++;

				} //end while there are line items to print out

			} /*end if there are stock movements to show on the invoice or credit note*/
			$YPos-= $line_height;
			/* check to see enough space left to print the 4 lines for the totals/footer */
			if (($YPos - $Bottom_Margin) < (2 * $line_height)) {
				PrintLinesToBottom($pdf, $Page_Height, $PageNumber, $FormDesign);
				include ('includes/PDFTransPageHeader.inc');
			}
			/* Print a column vertical line  with enough space for the footer */
			/* draw the vertical column lines to 4 lines shy of the bottom to leave space for invoice footer info ie totals etc */
			$pdf->line($FormDesign->DataLines->Line1->startx, $Page_Height - $FormDesign->DataLines->Line1->starty, $FormDesign->DataLines->Line1->endx, $Page_Height - $FormDesign->DataLines->Line1->endy);
			/* Print a column vertical line */
			$pdf->line($FormDesign->DataLines->Line2->startx, $Page_Height - $FormDesign->DataLines->Line2->starty, $FormDesign->DataLines->Line2->endx, $Page_Height - $FormDesign->DataLines->Line2->endy);
			/* Print a column vertical line */
			$pdf->line($FormDesign->DataLines->Line3->startx, $Page_Height - $FormDesign->DataLines->Line3->starty, $FormDesign->DataLines->Line3->endx, $Page_Height - $FormDesign->DataLines->Line3->endy);
			/* Print a column vertical line */
			$pdf->line($FormDesign->DataLines->Line4->startx, $Page_Height - $FormDesign->DataLines->Line4->starty, $FormDesign->DataLines->Line4->endx, $Page_Height - $FormDesign->DataLines->Line4->endy);
			/* Print a column vertical line */
			$pdf->line($FormDesign->DataLines->Line5->startx, $Page_Height - $FormDesign->DataLines->Line5->starty, $FormDesign->DataLines->Line5->endx, $Page_Height - $FormDesign->DataLines->Line5->endy);
			$pdf->line($FormDesign->DataLines->Line6->startx, $Page_Height - $FormDesign->DataLines->Line6->starty, $FormDesign->DataLines->Line6->endx, $Page_Height - $FormDesign->DataLines->Line6->endy);
			/* Rule off at bottom of the vertical lines */
			$pdf->line($FormDesign->LineAboveFooter->startx, $Page_Height - $FormDesign->LineAboveFooter->starty, $FormDesign->LineAboveFooter->endx, $Page_Height - $FormDesign->LineAboveFooter->endy);
			/* Now print out the footer and totals */
			if ($InvOrCredit == 'Invoice') {
				$DisplaySubTot = number_format($myrow['ovamount'], 2);
				$DisplayFreight = number_format($myrow['ovfreight'], 2);
				$DisplayTax = number_format($myrow['ovgst'], 2);
				$DisplayTotal = number_format($myrow['ovfreight'] + $myrow['ovgst'] + $myrow['ovamount'], 2);
			} else {
				$DisplaySubTot = number_format(-$myrow['ovamount'], 2);
				$DisplayFreight = number_format(-$myrow['ovfreight'], 2);
				$DisplayTax = number_format(-$myrow['ovgst'], 2);
				$DisplayTotal = number_format(-$myrow['ovfreight'] - $myrow['ovgst'] - $myrow['ovamount'], 2);
			}
			/* Print out the payment terms */
			$pdf->addTextWrap($FormDesign->PaymentTerms->x, $Page_Height - $FormDesign->PaymentTerms->y, $FormDesign->PaymentTerms->Length, $FormDesign->PaymentTerms->FontSize, _('Payment Terms') . ': ' . $myrow['terms']);
			//      $pdf->addText($Page_Width-$Right_Margin-392, $YPos - ($line_height*3)+22,$FontSize, _('Bank Code:***** Bank Account:*****'));
			//	$FontSize=10;
			$LeftOvers=explode('\r\n',DB_escape_string($myrow['invtext']));
			for ($i=0;$i<sizeOf($LeftOvers);$i++) {
				$pdf->addText($FormDesign->InvoiceText->x, $Page_Height - $FormDesign->InvoiceText->y-($i*10), $FormDesign->InvoiceText->FontSize, $LeftOvers[$i]);
			}
			$pdf->addText($FormDesign->SubTotalCaption->x, $Page_Height - $FormDesign->SubTotalCaption->y, $FormDesign->SubTotalCaption->FontSize, _('Sub Total'));
			$LeftOvers = $pdf->addTextWrap($FormDesign->SubTotal->x, $Page_Height - $FormDesign->SubTotal->y, $FormDesign->SubTotal->Length, $FormDesign->SubTotal->FontSize, $DisplaySubTot, 'right');
			$pdf->addText($FormDesign->FreightCaption->x, $Page_Height - $FormDesign->FreightCaption->y, $FormDesign->FreightCaption->FontSize, _('Freight'));
			$LeftOvers = $pdf->addTextWrap($FormDesign->Freight->x, $Page_Height - $FormDesign->Freight->y, $FormDesign->Freight->Length, $FormDesign->Freight->FontSize, $DisplayFreight, 'right');
			$pdf->addText($FormDesign->TaxCaption->x, $Page_Height - $FormDesign->TaxCaption->y, $FormDesign->TaxCaption->FontSize, _('Tax'));
			$LeftOvers = $pdf->addTextWrap($FormDesign->Tax->x, $Page_Height - $FormDesign->Tax->y, $FormDesign->Tax->Length, $FormDesign->Tax->FontSize, $DisplayTax, 'right');
			/*rule off for total */
			$pdf->line($FormDesign->TotalLine->startx, $Page_Height - $FormDesign->TotalLine->starty, $FormDesign->TotalLine->endx, $Page_Height - $FormDesign->TotalLine->endy);
			/*vertical to separate totals from comments and ROMALPA */
			$pdf->line($FormDesign->RomalpaLine->startx, $Page_Height - $FormDesign->RomalpaLine->starty, $FormDesign->RomalpaLine->endx, $Page_Height - $FormDesign->RomalpaLine->endy);
			if ($InvOrCredit == 'Invoice') {
				$pdf->addText($FormDesign->TotalCaption->x, $Page_Height - $FormDesign->TotalCaption->y, $FormDesign->TotalCaption->FontSize, _('TOTAL INVOICE'));
				$YPos=$FormDesign->Romalpa->y;
				$LeftOvers = $pdf->addTextWrap($FormDesign->Romalpa->x, $Page_Height - $YPos, $FormDesign->Romalpa->Length, $FormDesign->Romalpa->FontSize, $_SESSION['RomalpaClause']);
				while (strlen($LeftOvers) > 0 AND ($Page_Height - $YPos) > $Bottom_Margin) {
					$YPos+= $FormDesign->Romalpa->FontSize+1;
					$LeftOvers = $pdf->addTextWrap($FormDesign->Romalpa->x, $Page_Height - $YPos, $FormDesign->Romalpa->Length, $FormDesign->Romalpa->FontSize, $LeftOvers);
				}
				/* Add Images for Visa / Mastercard / Paypal */
				if (file_exists('companies/' . $_SESSION['DatabaseName'] . '/payment.jpg')) {
					$pdf->addJpegFromFile('companies/' . $_SESSION['DatabaseName'] . '/payment.jpg', $FormDesign->CreditCardLogo->x,$Page_Height -$FormDesign->CreditCardLogo->y,$FormDesign->CreditCardLogo->width,$FormDesign->CreditCardLogo->height);
				}
//				$pdf->addText($Page_Width - $Right_Margin - 472, $YPos - ($line_height * 3) + 32, $FontSize, '');
			} else {
				$pdf->addText($FormDesign->TotalCaption->x, $Page_Height - $FormDesign->TotalCaption->y, $FormDesign->TotalCaption->FontSize, _('TOTAL CREDIT'));
			}
			$LeftOvers = $pdf->addTextWrap($FormDesign->Total->x, $Page_Height - $FormDesign->Total->y, $FormDesign->Total->Length, $FormDesign->Total->FontSize, $DisplayTotal, 'right');
		} /* end of check to see that there was an invoice record to print */
		$FromTransNo++;
	}
} /* end loop to print invoices */
// Start FPDI concatination to append PDF files conditionally to the invoice
// This part taken from FPDI example page -not used yet since change to TCPDF Dec 2009
if (isset($PrintPDF)) {
	class concat_pdf extends FPDI {
		var $files = array();
		function setFiles($files) {
			$this->files = $files;
		}
		function concat() {
			foreach($this->files as $file) {
				if ($file != 'pdf_append/none') {
					$pagecount = $this->setSourceFile($file);
					for ($i = 1;$i <= $pagecount;$i++) {
						$tplidx = $this->ImportPage($i);
						$s = $this->getTemplatesize($tplidx);
						$this->AddPage($s['h'] > $s['w'] ? 'P' : 'L');
						$this->useTemplate($tplidx);
					}
				}
			}
		}
	}
}
// $pdf =& new concat_pdf();
// Have to get the TransNo again, GET[FromTransNo] is updated on each pass of loop
if (isset($_GET['FromTransNo'])) {
	$FromTransNo = trim($_GET['FromTransNo']);
} elseif (isset($_POST['FromTransNo'])) {
	$FromTransNo = trim($_POST['FromTransNo']);
}
if (!isset($InvOrCredit)) {
	$InvOrCredit='';
}
if (($InvOrCredit == 'Invoice' or $InvOrCredit == 'Credit') and isset($PrintPDF)) {
	$sql = "SELECT stockmoves.stockid, stockmaster.appendfile
			FROM stockmoves, stockmaster
			WHERE stockmoves.stockid = stockmaster.stockid
			AND stockmoves.type=10
			AND stockmoves.transno='" . $FromTransNo . "'
			AND stockmoves.show_on_inv_crds=1";
	// };
	if ($FromTransNo!='Preview') {
		$result = DB_query($sql, $db);
	}
	// Loop the result set and add appendfile if the field is not 0 or none
	while (isset($result) and $row = DB_fetch_array($result)) {
		if ($row['appendfile'] != '0' AND $row['appendfile'] !== 'none') {
			$pdf->setFiles(array($_SESSION['reports_dir'] . '/Invoice.pdf', 'companies/' . $_SESSION['DatabaseName'] . '/pdf_append/' . $row['appendfile']));
			$pdf->concat();
			$pdf->Output($_SESSION['CompanyRecord']['coyname'] . '_Invoice.pdf', 'I');
			exit;
			// If EMAIL is selected, send the invoice via email, this is not appending pages yet though

		} elseif (isset($_GET['Email'])) {
			$pdf->Output($_SESSION['reports_dir'] . '/Invoice.pdf', 'F');
		} else {
			// If the appendfile field is empty and EMAIL is not selected, just print the invoice without any appended pages
			$pdf->Output($_SESSION['CompanyRecord']['coyname'] . '_Invoice.pdf', 'I');
			exit;
		}
	}
	//End FPDI Concat
	if (isset($_GET['Email'])) { //email the invoice to address supplied
		include ('includes/header.inc');
		include ('includes/htmlMimeMail.php');
		$mail = new htmlMimeMail();
		$filename = $_SESSION['reports_dir'] . '/Invoice.pdf';
		$fp = fopen($_SESSION['reports_dir'] . '/Invoice.pdf', 'wb');
		fwrite($fp, $pdfcode);
		fclose($fp);
		$attachment = $mail->getFile($filename);
		$mail->setText(_('Please find attached') . ' ' . $InvOrCredit . ' ' . $_GET['FromTransNo']);
		$mail->SetSubject($InvOrCredit . ' ' . $_GET['FromTransNo']);
		$mail->addAttachment($attachment, $filename, 'application/pdf');
		$mail->setFrom($_SESSION['CompanyRecord']['coyname'] . ' <' . $_SESSION['CompanyRecord']['email'] . '>');
		$result = $mail->send(array($_GET['Email']));
		unlink($filename); //delete the temporary file
		$title = _('Emailing') . ' ' . $InvOrCredit . ' ' . _('Number') . ' ' . $FromTransNo;
		include ('includes/header.inc');
		echo "<p>$InvOrCredit " . _('number') . ' ' . $_GET['FromTransNo'] . ' ' . _('has been emailed to') . ' ' . $_GET['Email'];
		include ('includes/footer.inc');
		exit;
	} else {
		$pdf->OutputD('PrintCustTrans.pdf');
		$pdf->__destruct();
	}
} else { /*The option to print PDF was not hit */
	$title = _('Select Invoices/Credit Notes To Print');
	include ('includes/header.inc');
	if (!isset($FromTransNo) OR $FromTransNo == '') {
		/* if FromTransNo is not set then show a form to allow input of either a single invoice number or a range of invoices to be printed. Also get the last invoice number created to show the user where the current range is up to */
		echo "<form action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'><table class='selection'>";
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<div class="centre"><p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Print') . '" alt="">' . ' ' . _('Print Invoices or Credit Notes (Landscape Mode)') . '</div>';
		echo '<tr><td>' . _('Print Invoices or Credit Notes') . '</td><td><select name=InvOrCredit>';
		if ($InvOrCredit == 'Invoice' OR !isset($InvOrCredit)) {
			echo "<option selected VALUE='Invoice'>" . _('Invoices');
			echo "<option VALUE='Credit'>" . _('Credit Notes');
		} else {
			echo "<option selected VALUE='Credit'>" . _('Credit Notes');
			echo "<option VALUE='Invoice'>" . _('Invoices');
		}
		echo '</select></td></tr>';
		echo '<tr><td>' . _('Print EDI Transactions') . '</td><td><select name=PrintEDI>';
		if ($InvOrCredit == 'Invoice' OR !isset($InvOrCredit)) {
			echo "<option selected VALUE='No'>" . _('Do not Print PDF EDI Transactions');
			echo "<option VALUE='Yes'>" . _('Print PDF EDI Transactions Too');
		} else {
			echo "<option VALUE='No'>" . _('Do not Print PDF EDI Transactions');
			echo "<option selected VALUE='Yes'>" . _('Print PDF EDI Transactions Too');
		}
		echo '</select></td></tr>';
		echo '<tr><td>' . _('Start invoice/credit note number to print') . '</td><td><input Type=text class=number max=6 size=7 name=FromTransNo></td></tr>';
		echo '<tr><td>' . _('End invoice/credit note number to print') . "</td><td><input Type=text class=number max=6 size=7 name='ToTransNo'></td></tr></table>";
		echo "<br><div class='centre'><input type=Submit Name='Print' Value='" . _('Print') . "'><p>";
		echo "<input type=Submit Name='PrintPDF' Value='" . _('Print PDF') . "'></div>";
		$sql = 'SELECT typeno FROM systypes WHERE typeid=10';
		$result = DB_query($sql, $db);
		$myrow = DB_fetch_row($result);
		echo '<div class="page_help_text"><b>' . _('The last invoice created was number') . ' ' . $myrow[0] . '</b><br>' . _('If only a single invoice is required') . ', ' . _('enter the invoice number to print in the Start transaction number to print field and leave the End transaction number to print field blank') . '. ' . _('Only use the end invoice to print field if you wish to print a sequential range of invoices') . '';
		$sql = 'SELECT typeno FROM systypes WHERE typeid=11';
		$result = DB_query($sql, $db);
		$myrow = DB_fetch_row($result);
		echo '<br /><b>' . _('The last credit note created was number') . ' ' . $myrow[0] . '</b><br />' . _('A sequential range can be printed using the same method as for invoices above') . '. ' . _('A single credit note can be printed by only entering a start transaction number') . '</DIV';
	} else {
		while ($FromTransNo <= $_POST['ToTransNo']) {
			/*retrieve the invoice details from the database to print
			notice that salesorder record must be present to print the invoice purging of sales orders will
			nobble the invoice reprints */
			if ($InvOrCredit == 'Invoice') {
				$sql = "SELECT
						debtortrans.trandate,
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
						salesman.salesmanname,
						debtortrans.debtorno
					FROM debtortrans,
						debtorsmaster,
						custbranch,
						salesorders,
						shippers,
						salesman
					WHERE debtortrans.order_ = salesorders.orderno
					AND debtortrans.type=10
					AND debtortrans.transno='" . $FromTransNo . "'
					AND debtortrans.shipvia=shippers.shipper_id
					AND debtortrans.debtorno=debtorsmaster.debtorno
					AND debtortrans.debtorno=custbranch.debtorno
					AND debtortrans.branchcode=custbranch.branchcode
					AND custbranch.salesman=salesman.salesmancode";
			} else {
				$sql = "SELECT debtortrans.trandate,
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
						salesman.salesmanname,
						debtortrans.debtorno
					FROM debtortrans,
						debtorsmaster,
						custbranch,
						salesman
					WHERE debtortrans.type=11
					AND debtortrans.transno='" . $FromTransNo . "'
					AND debtortrans.debtorno=debtorsmaster.debtorno
					AND debtortrans.debtorno=custbranch.debtorno
					AND debtortrans.branchcode=custbranch.branchcode
					AND custbranch.salesman=salesman.salesmancode";
			}
			$result = DB_query($sql, $db);
			if (DB_num_rows($result) == 0 OR DB_error_no($db) != 0) {
				echo '<div class="page_help_text">' . _('There was a problem retrieving the invoice or credit note details for note number') . ' ' . $FromTransNo . ' ' . _('from the database') . '. ' . _('To print an invoice, the sales order record, the customer transaction record and the branch record for the customer must not have been purged') . '. ' . _('To print a credit note only requires the customer, transaction, salesman and branch records be available').'</div>';
				if ($debug == 1) {
					echo _('The SQL used to get this information that failed was') . "<br>$sql";
				}
				break;
				include ('includes/footer.inc');
				exit;
			} elseif (DB_num_rows($result) == 1) {
				$myrow = DB_fetch_array($result);
				/* Then there's an invoice (or credit note) to print. So print out the invoice header and GST Number from the company record */
				if (count($_SESSION['AllowedPageSecurityTokens']) == 1 AND in_array(1, $_SESSION['AllowedPageSecurityTokens']) AND $myrow['debtorno'] != $_SESSION['CustomerID']) {
					echo '<p><font color=RED size=4>' . _('This transaction is addressed to another customer and cannot be displayed for privacy reasons') . '. ' . _('Please select only transactions relevant to your company');
					exit;
				}
				$ExchRate = $myrow['rate'];
				$PageNumber = 1;
				echo "<table class='table1'><tr><td VALIGN=TOP WIDTH=10%><img src='" . $_SESSION['LogoFile'] . "'></td><td bgcolor='#BBBBBB'><b>";
				if ($InvOrCredit == 'Invoice') {
					echo '<font size=4>' . _('TAX INVOICE') . ' ';
				} else {
					echo '<font color=RED size=4>' . _('TAX CREDIT NOTE') . ' ';
				}
				echo '</b>' . _('Number') . ' ' . $FromTransNo . '</font><br><font size=1>' . _('Tax Authority Ref') . '. ' . $_SESSION['CompanyRecord']['gstno'] . '</td></tr></table>';
				/* Now print out the logo and company name and address */
				echo "<table class='table1'><tr><td><font size=4 color='#333333'><b>" . $_SESSION['CompanyRecord']['coyname'] . "</b></font><br>";
				echo $_SESSION['CompanyRecord']['regoffice1'] . '<br>';
				echo $_SESSION['CompanyRecord']['regoffice2'] . '<br>';
				echo $_SESSION['CompanyRecord']['regoffice3'] . '<br>';
				echo $_SESSION['CompanyRecord']['regoffice4'] . '<br>';
				echo $_SESSION['CompanyRecord']['regoffice5'] . '<br>';
				echo $_SESSION['CompanyRecord']['regoffice6'] . '<br>';
				echo _('Telephone') . ': ' . $_SESSION['CompanyRecord']['telephone'] . '<br>';
				echo _('Facsimile') . ': ' . $_SESSION['CompanyRecord']['fax'] . '<br>';
				echo _('Email') . ': ' . $_SESSION['CompanyRecord']['email'] . '<br>';
				echo '</td><td WIDTH=50% class=number>';
				/* Now the customer charged to details in a sub table within a cell of the main table*/
				echo "<table class='table1'><tr><td align=left bgcolor='#BBBBBB'><b>" . _('Charge To') . ":</b></td></tr><tr><td bgcolor='#EEEEEE'>";
				echo $myrow['name'] . '<br>' . $myrow['address1'] . '<br>' . $myrow['address2'] . '<br>' . $myrow['address3'] . '<br>' . $myrow['address4'] . '<br>' . $myrow['address5'] . '<br>' . $myrow['address6'];
				echo '</td></tr></table>';
				/*end of the small table showing charge to account details */
				echo _('Page') . ': ' . $PageNumber;
				echo '</td></tr></table>';
				/*end of the main table showing the company name and charge to details */
				if ($InvOrCredit == 'Invoice') {
					echo "<table class='table1'>
				   			<tr>
				   				<td align=left bgcolor='#BBBBBB'><b>" . _('Charge Branch') . ":</b></td>
								<td align=left bgcolor='#BBBBBB'><b>" . _('Delivered To') . ":</b></td>
							</tr>";
					echo "<tr>
				   		<td bgcolor='#EEEEEE'>" . $myrow['brname'] . '<br>' . $myrow['braddress1'] . '<br>' . $myrow['braddress2'] . '<br>' . $myrow['braddress3'] . '<br>' . $myrow['braddress4'] . '<br>' . $myrow['braddress5'] . '<br>' . $myrow['braddress6'] . '</td>';
					echo "<td bgcolor='#EEEEEE'>" . $myrow['deliverto'] . '<br>' . $myrow['deladd1'] . '<br>' . $myrow['deladd2'] . '<br>' . $myrow['deladd3'] . '<br>' . $myrow['deladd4'] . '<br>' . $myrow['deladd5'] . '<br>' . $myrow['deladd6'] . '</td>';
					echo '</tr>
				   </table><hr>';
					echo "<table class='table1'>
				   		<tr>
							<td align=left bgcolor='#BBBBBB'><b>" . _('Your Order Ref') . "</b></td>
							<td align=left bgcolor='#BBBBBB'><b>" . _('Our Order No') . "</b></td>
							<td align=left bgcolor='#BBBBBB'><b>" . _('Order Date') . "</b></td>
							<td align=left bgcolor='#BBBBBB'><b>" . _('Invoice Date') . "</b></td>
							<td align=left bgcolor='#BBBBBB'><b>" . _('Sales Person') . "</font></b></td>
							<td align=left bgcolor='#BBBBBB'><b>" . _('Shipper') . "</b></td>
							<td align=left bgcolor='#BBBBBB'><b>" . _('Consignment Ref') . "</b></td>
						</tr>";
					echo "<tr>
							<td bgcolor='#EEEEEE'>" . $myrow['customerref'] . "</td>
							<td bgcolor='#EEEEEE'>" . $myrow['orderno'] . "</td>
							<td bgcolor='#EEEEEE'>" . ConvertSQLDate($myrow['orddate']) . "</td>
							<td bgcolor='#EEEEEE'>" . ConvertSQLDate($myrow['trandate']) . "</td>
							<td bgcolor='#EEEEEE'>" . $myrow['salesmanname'] . "</td>
							<td bgcolor='#EEEEEE'>" . $myrow['shippername'] . "</td>
							<td bgcolor='#EEEEEE'>" . $myrow['consignment'] . "</td>
						</tr>
					</table>";
					$sql = "SELECT stockmoves.stockid,
				   		stockmaster.description,
						-stockmoves.qty as quantity,
						stockmoves.discountpercent,
						((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . "* -stockmoves.qty) AS fxnet,
						(stockmoves.price * " . $ExchRate . ") AS fxprice,
						stockmoves.narrative,
						stockmaster.units
					FROM stockmoves,
						stockmaster
					WHERE stockmoves.stockid = stockmaster.stockid
					AND stockmoves.type=10
					AND stockmoves.transno='" . $FromTransNo . "'
					AND stockmoves.show_on_inv_crds=1";
				} else { /* then its a credit note */
					echo "<table WIDTH=50%><tr>
				   		<td align=left bgcolor='#BBBBBB'><b>" . _('Branch') . ":</b></td>
						</tr>";
					echo "<tr>
				   		<td bgcolor='#EEEEEE'>" . $myrow['brname'] . '<br>' . $myrow['braddress1'] . '<br>' . $myrow['braddress2'] . '<br>' . $myrow['braddress3'] . '<br>' . $myrow['braddress4'] . '<br>' . $myrow['braddress5'] . '<br>' . $myrow['braddress6'] . '</td>
					</tr></table>';
					echo "<hr><table class='table1'><tr>
				   		<td align=left bgcolor='#BBBBBB'><b>" . _('Date') . "</b></td>
						<td align=left bgcolor='#BBBBBB'><b>" . _('Sales Person') . "</font></b></td>
					</tr>";
					echo "<tr>
				   		<td bgcolor='#EEEEEE'>" . ConvertSQLDate($myrow['trandate']) . "</td>
						<td bgcolor='#EEEEEE'>" . $myrow['salesmanname'] . '</td>
					</tr></table>';
					$sql = "SELECT stockmoves.stockid,
				   		stockmaster.description,
						stockmoves.qty as quantity,
						stockmoves.discountpercent, ((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . " * stockmoves.qty) AS fxnet,
						(stockmoves.price * " . $ExchRate . ") AS fxprice,
						stockmaster.units
					FROM stockmoves,
						stockmaster
					WHERE stockmoves.stockid = stockmaster.stockid
					AND stockmoves.type=11
					AND stockmoves.transno='" . $FromTransNo . "'
					AND stockmoves.show_on_inv_crds=1";
				}
				echo '<hr>';
				echo '<div class="centre"><font size=2>' . _('All amounts stated in') . ' ' . $myrow['currcode'] . '</font></div>';
				$result = DB_query($sql, $db);
				if (DB_error_no($db) != 0) {
					echo '<div class="page_help_text">' . _('There was a problem retrieving the invoice or credit note stock movement details for invoice number') . ' ' . $FromTransNo . ' ' . _('from the database').'</div>';
					if ($debug == 1) {
						echo '<br>' . _('The SQL used to get this information that failed was') . "<br>$sql";
					}
					exit;
				}
				if (DB_num_rows($result) > 0) {
					echo "<table class='table1'>
						<tr><th>" . _('Item Code') . "</th>
						<th>" . _('Item Description') . "</th>
						<th>" . _('Quantity') . "</th>
						<th>" . _('Unit') . "</th>
						<th>" . _('Price') . "</th>
						<th>" . _('Discount') . "</th>
						<th>" . _('Net') . '</th></tr>';
					$LineCounter = 17;
					$k = 0; //row colour counter
					while ($myrow2 = DB_fetch_array($result)) {
						if ($k == 1) {
							$RowStarter = '<tr class="EvenTableRows">';
							$k = 0;
						} else {
							$RowStarter = '<tr class="OddTableRows">';
							$k = 1;
						}
						echo $RowStarter;
						$DisplayPrice = number_format($myrow2['fxprice'], 2);
						$DisplayQty = number_format($myrow2['quantity'], 2);
						$DisplayNet = number_format($myrow2['fxnet'], 2);
						if ($myrow2['discountpercent'] == 0) {
							$DisplayDiscount = '';
						} else {
							$DisplayDiscount = number_format($myrow2['discountpercent'] * 100, 2) . '%';
						}
						printf('<td>%s</td>
					      		<td>%s</td>
							<td class=number>%s</td>
							<td class=number>%s</td>
							<td class=number>%s</td>
							<td class=number>%s</td>
							<td class=number>%s</td>
							</tr>', $myrow2['stockid'], $myrow2['description'], $DisplayQty, $myrow2['units'], $DisplayPrice, $DisplayDiscount, $DisplayNet);
						if (strlen($myrow2['narrative']) > 1) {
							echo $RowStarter . '<td></td><td colspan=6>' . $myrow2['narrative'] . '</td></tr>';
							$LineCounter++;
						}
						$LineCounter++;
						if ($LineCounter == ($_SESSION['PageLength'] - 2)) {
							/* head up a new invoice/credit note page */
							$PageNumber++;
							echo "</table><table class='table1'><tr><td VALIGN=TOp><img src='" . $_SESSION['LogoFile'] . "'></td><td bgcolor='#BBBBBB'><b>";
							if ($InvOrCredit == 'Invoice') {
								echo '<font size=4>' . _('TAX INVOICE') . ' ';
							} else {
								echo '<font color=RED size=4>' . _('TAX CREDIT NOTE') . ' ';
							}
							echo '</b>' . _('Number') . ' ' . $FromTransNo . '</font><br /><font size=1>' . _('GST Number') . ' - ' . $_SESSION['CompanyRecord']['gstno'] . '</td></tr></table>';
							/*Now print out company name and address */
							echo "<table class='table1'><tr>
						    	<td><font size=4 color='#333333'><b>" . $_SESSION['CompanyRecord']['coyname'] . '</b></font><br>';
							echo $_SESSION['CompanyRecord']['regoffice1'] . '<br>';
							echo $_SESSION['CompanyRecord']['regoffice2'] . '<br>';
							echo $_SESSION['CompanyRecord']['regoffice3'] . '<br>';
							echo $_SESSION['CompanyRecord']['regoffice4'] . '<br>';
							echo $_SESSION['CompanyRecord']['regoffice5'] . '<br>';
							echo $_SESSION['CompanyRecord']['regoffice6'] . '<br>';
							echo _('Telephone') . ': ' . $_SESSION['CompanyRecord']['telephone'] . '<br>';
							echo _('Facsimile') . ': ' . $_SESSION['CompanyRecord']['fax'] . '<br>';
							echo _('Email') . ': ' . $_SESSION['CompanyRecord']['email'] . '<br>';
							echo '</td><td class=number>' . _('Page') . ": $PageNumber</td></tr></table>";
							echo "<table class='table1'><tr>
						    	<th>" . _('Item Code') . "</th>
							<th>" . _('Item Description') . "</th>
							<th>" . _('Quantity') . "</th>
							<th>" . _('Unit') . "</th>
							<th>" . _('Price') . "</th>
							<th>" . _('Discount') . "</th>
							<th>" . _('Net') . "</th></tr>";
							$LineCounter = 10;
						} //end if need a new page headed up

					} //end while there are line items to print out
					echo '</table>';
				} /*end if there are stock movements to show on the invoice or credit note*/
				/* check to see enough space left to print the totals/footer */
				$LinesRequiredForText = floor(strlen($myrow['invtext']) / 140);
				if ($LineCounter >= ($_SESSION['PageLength'] - 8 - $LinesRequiredForText)) {
					/* head up a new invoice/credit note page */
					$PageNumber++;
					echo "<table class='table1'><tr><td VALIGN=TOp><img src='" . $_SESSION['LogoFile'] . "'></td><td bgcolor='#BBBBBB'><b>";
					if ($InvOrCredit == 'Invoice') {
						echo '<font size=4>' . _('TAX INVOICE') . ' ';
					} else {
						echo '<font color=RED size=4>' . _('TAX CREDIT NOTE') . ' ';
					}
					echo '</b>' . _('Number') . ' ' . $FromTransNo . '</font><br><font size=1>' . _('GST Number') . ' - ' . $_SESSION['CompanyRecord']['gstno'] . '</td></tr><table>';
					/* Print out the logo and company name and address */
					echo "<table class='table1'><tr><td><font size=4 color='#333333'><b>" . $_SESSION['CompanyRecord']['coyname'] . "</b></font><br>";
					echo $_SESSION['CompanyRecord']['regoffice1'] . '<br>';
					echo $_SESSION['CompanyRecord']['regoffice2'] . '<br>';
					echo $_SESSION['CompanyRecord']['regoffice3'] . '<br>';
					echo $_SESSION['CompanyRecord']['regoffice4'] . '<br>';
					echo $_SESSION['CompanyRecord']['regoffice5'] . '<br>';
					echo $_SESSION['CompanyRecord']['regoffice6'] . '<br>';
					echo _('Telephone') . ': ' . $_SESSION['CompanyRecord']['telephone'] . '<br>';
					echo _('Facsimile') . ': ' . $_SESSION['CompanyRecord']['fax'] . '<br>';
					echo _('Email') . ': ' . $_SESSION['CompanyRecord']['email'] . '<br>';
					echo '</td><td class=number>' . _('Page') . ": $PageNumber</td></tr></table>";
					echo "<table class='table1'><tr>
						<th>" . _('Item Code') . "</th>
						<th>" . _('Item Description') . "</th>
						<th>" . _('Quantity') . "</th>
						<th>" . _('Unit') . "</th>
						<th>" . _('Price') . "</th>
						<th>" . _('Discount') . "</th>
						<th>" . _('Net') . '</th></tr>';
					$LineCounter = 10;
				}
				/* Space out the footer to the bottom of the page */
				echo '<br><br>' . $myrow['invtext'];
				$LineCounter = $LineCounter + 2 + $LinesRequiredForText;
				while ($LineCounter < ($_SESSION['PageLength'] - 6)) {
					echo '<br>';
					$LineCounter++;
				}
				/* Now print out the footer and totals */
				if ($InvOrCredit == 'Invoice') {
					$DisplaySubTot = number_format($myrow['ovamount'], 2);
					$DisplayFreight = number_format($myrow['ovfreight'], 2);
					$DisplayTax = number_format($myrow['ovgst'], 2);
					$DisplayTotal = number_format($myrow['ovfreight'] + $myrow['ovgst'] + $myrow['ovamount'], 2);
				} else {
					$DisplaySubTot = number_format(-$myrow['ovamount'], 2);
					$DisplayFreight = number_format(-$myrow['ovfreight'], 2);
					$DisplayTax = number_format(-$myrow['ovgst'], 2);
					$DisplayTotal = number_format(-$myrow['ovfreight'] - $myrow['ovgst'] - $myrow['ovamount'], 2);
				}
				/*Print out the invoice text entered */
				echo '<table class=table1><tr>
					<td class=number>' . _('Sub Total') . "</td>
					<td class=number bgcolor='#EEEEEE' WIDTH=15%>$DisplaySubTot</td></tr>";
				echo '<tr><td class=number>' . _('Freight') . "</td>
					<td class=number bgcolor='#EEEEEE'>$DisplayFreight</td></tr>";
				echo '<tr><td class=number>' . _('Tax') . "</td>
					<td class=number bgcolor='#EEEEEE'>$DisplayTax</td></tr>";
				if ($InvOrCredit == 'Invoice') {
					echo '<tr><td class=number><b>' . _('TOTAL INVOICE') . "</b></td>
				     	<td class=number bgcolor='#EEEEEE'><U><b>$DisplayTotal</b></U></td></tr>";
				} else {
					echo '<tr><td class=number><font color=RED><b>' . _('TOTAL CREDIT') . "</b></font></td>
				     		<td class=number bgcolor='#EEEEEE'><font color=RED><U><b>$DisplayTotal</b></U></font></td></tr>";
				}
				echo '</table>';
			} /* end of check to see that there was an invoice record to print */
			$FromTransNo++;
		} /* end loop to print invoices */
	} /*end of if FromTransNo exists */
	include ('includes/footer.inc');
} /*end of else not PrintPDF */
function PrintLinesToBottom($pdf, $Page_Height, $PageNumber, $FormDesign) {
	/* draw the vertical column lines right to the bottom */
	$pdf->line($FormDesign->DataLines->Line1->startx, $Page_Height - $FormDesign->DataLines->Line1->starty, $FormDesign->DataLines->Line1->endx, $Page_Height - $FormDesign->DataLines->Line1->endy);
	/* Print a column vertical line */
	$pdf->line($FormDesign->DataLines->Line2->startx, $Page_Height - $FormDesign->DataLines->Line2->starty, $FormDesign->DataLines->Line2->endx, $Page_Height - $FormDesign->DataLines->Line2->endy);
	/* Print a column vertical line */
	$pdf->line($FormDesign->DataLines->Line3->startx, $Page_Height - $FormDesign->DataLines->Line3->starty, $FormDesign->DataLines->Line3->endx, $Page_Height - $FormDesign->DataLines->Line3->endy);
	/* Print a column vertical line */
	$pdf->line($FormDesign->DataLines->Line4->startx, $Page_Height - $FormDesign->DataLines->Line4->starty, $FormDesign->DataLines->Line4->endx, $Page_Height - $FormDesign->DataLines->Line4->endy);
	/* Print a column vertical line */
	$pdf->line($FormDesign->DataLines->Line5->startx, $Page_Height - $FormDesign->DataLines->Line5->starty, $FormDesign->DataLines->Line5->endx, $Page_Height - $FormDesign->DataLines->Line5->endy);
	$pdf->line($FormDesign->DataLines->Line6->startx, $Page_Height - $FormDesign->DataLines->Line6->starty, $FormDesign->DataLines->Line6->endx, $Page_Height - $FormDesign->DataLines->Line6->endy);
	//	$pdf->newPage();
	$PageNumber++;
}
?>
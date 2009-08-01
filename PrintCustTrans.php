<?php

/* $Revision: 1.49 $ */

$PageSecurity = 1;

error_reporting (E_ALL);

include('includes/session.inc');

if (isset($_GET['FromTransNo'])){
	$FromTransNo = trim($_GET['FromTransNo']);
} elseif (isset($_POST['FromTransNo'])){
	$FromTransNo = trim($_POST['FromTransNo']);
}


if (isset($_GET['InvOrCredit'])){
	$InvOrCredit = $_GET['InvOrCredit'];
} elseif (isset($_POST['InvOrCredit'])){
	$InvOrCredit = $_POST['InvOrCredit'];
}
if (isset($_GET['PrintPDF'])){
	$PrintPDF = $_GET['PrintPDF'];
} elseif (isset($_POST['PrintPDF'])){
	$PrintPDF = $_POST['PrintPDF'];
}


If (!isset($_POST['ToTransNo'])
	OR trim($_POST['ToTransNo'])==''
	OR $_POST['ToTransNo'] < $FromTransNo){

	$_POST['ToTransNo'] = $FromTransNo;
}

$FirstTrans = $FromTransNo; /*Need to start a new page only on subsequent transactions */

If (isset($PrintPDF) or isset($_GET['PrintPDF'])
	AND $PrintPDF!=''
	AND isset($FromTransNo)
	AND isset($InvOrCredit)
	AND $FromTransNo!=''){

	include ('includes/class.pdf.php');
//	define('FPDF_FONTPATH','font/');
	require('fpdi/fpdi.php');

	/*
	All this lot unnecessary if session.inc included at the start
	previously it was not possible to start a session before initiating a class
	include('config.php');
	include('includes/ConnectDB.inc');
	include('includes/GetConfig.php');
	include('includes/DateFunctions.inc');
	if (isset($SessionSavePath)){
		session_save_path($SessionSavePath);
	}
	ini_set('session.gc_Maxlifetime',$SessionLifeTime);
	ini_set('max_execution_time',$MaximumExecutionTime);

	session_start();
	*/

	/*This invoice is hard coded for A4 Landscape invoices or credit notes  so can't use PDFStarter.inc*/
	$Page_Width=842;
	$Page_Height=595;
	$Top_Margin=30;
	$Bottom_Margin=30;
	$Left_Margin=40;
	$Right_Margin=30;

	$PageSize = array(0,0,$Page_Width,$Page_Height);

	$pdf = & new Cpdf($PageSize);
	$pdf->selectFont('helvetica');
	$pdf->addinfo('Author','webERP ' . $Version);
	$pdf->addinfo('Creator','webERP http://www.weberp.org');

	$FirstPage = true;

	if ($InvOrCredit=='Invoice'){
		$pdf->addinfo('Title',_('Sales Invoice') . ' ' . $FromTransNo . ' to ' . $_POST['ToTransNo']);
		$pdf->addinfo('Subject',_('Invoices from') . ' ' . $FromTransNo . ' ' . _('to') . ' ' . $_POST['ToTransNo']);
	} else {
		$pdf->addinfo('Title',_('Sales Credit Note') );
		$pdf->addinfo('Subject',_('Credit Notes from') . ' ' . $FromTransNo . ' ' . _('to') . ' ' . $_POST['ToTransNo']);
	}

	$line_height=16;

	while ($FromTransNo <= $_POST['ToTransNo']){

	/*retrieve the invoice details from the database to print
	notice that salesorder record must be present to print the invoice purging of sales orders will
	nobble the invoice reprints */

		if ($InvOrCredit=='Invoice') {
			$sql = 'SELECT debtortrans.trandate,
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
			AND debtortrans.transno=' . $FromTransNo . '
			AND debtortrans.shipvia=shippers.shipper_id
			AND debtortrans.debtorno=debtorsmaster.debtorno
			AND debtorsmaster.paymentterms=paymentterms.termsindicator
			AND debtortrans.debtorno=custbranch.debtorno
			AND debtortrans.branchcode=custbranch.branchcode
			AND custbranch.salesman=salesman.salesmancode
			AND salesorders.fromstkloc=locations.loccode';

		if (isset($_POST['PrintEDI']) and $_POST['PrintEDI']=='No'){
			$sql = $sql . ' AND debtorsmaster.ediinvoices=0';
		}
	} else {

		$sql = 'SELECT debtortrans.trandate,
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
			AND debtortrans.transno=' . $FromTransNo .'
			AND debtortrans.debtorno=debtorsmaster.debtorno
			AND debtortrans.debtorno=custbranch.debtorno
			AND debtortrans.branchcode=custbranch.branchcode
			AND custbranch.salesman=salesman.salesmancode';

		if ($_POST['PrintEDI']=='No'){
			$sql = $sql . ' AND debtorsmaster.ediinvoices=0';
		}
	   }
	   $result=DB_query($sql,$db,'','',false,false);

	   if (DB_error_no($db)!=0) {

		$title = _('Transaction Print Error Report');
		include ('includes/header.inc');

		prnMsg( _('There was a problem retrieving the invoice or credit note details for note number') . ' ' . $InvoiceToPrint . ' ' . _('from the database') . '. ' . _('To print an invoice, the sales order record, the customer transaction record and the branch record for the customer must not have been purged') . '. ' . _('To print a credit note only requires the customer, transaction, salesman and branch records be available'),'error');
		if ($debug==1){
		    prnMsg (_('The SQL used to get this information that failed was') . "<br>" . $sql,'error');
		}
		include ('includes/footer.inc');
		exit;
	   }
	   if (DB_num_rows($result)==1){
		$myrow = DB_fetch_array($result);

		$ExchRate = $myrow['rate'];

		if ($InvOrCredit=='Invoice'){

			 $sql = 'SELECT stockmoves.stockid,
					stockmaster.description,
					-stockmoves.qty as quantity,
					stockmoves.discountpercent,
					((1 - stockmoves.discountpercent) * stockmoves.price * ' . $ExchRate . '* -stockmoves.qty) AS fxnet,
					(stockmoves.price * ' . $ExchRate . ') AS fxprice,
					stockmoves.narrative,
					stockmaster.units
				FROM stockmoves,
					stockmaster
				WHERE stockmoves.stockid = stockmaster.stockid
				AND stockmoves.type=10
				AND stockmoves.transno=' . $FromTransNo . '
				AND stockmoves.show_on_inv_crds=1';
		} else {
		/* only credit notes to be retrieved */
			 $sql = 'SELECT stockmoves.stockid,
			 		stockmaster.description,
					stockmoves.qty as quantity,
					stockmoves.discountpercent,
					((1 - stockmoves.discountpercent) * stockmoves.price * ' . $ExchRate . ' * stockmoves.qty) AS fxnet,
					(stockmoves.price * ' . $ExchRate . ') AS fxprice,
					stockmoves.narrative,
					stockmaster.units
				FROM stockmoves,
					stockmaster
				WHERE stockmoves.stockid = stockmaster.stockid
				AND stockmoves.type=11
				AND stockmoves.transno=' . $FromTransNo . '
				AND stockmoves.show_on_inv_crds=1';
		}

		$result=DB_query($sql,$db);
		if (DB_error_no($db)!=0) {
			$title = _('Transaction Print Error Report');
			include ('includes/header.inc');
			echo '<br>' . _('There was a problem retrieving the invoice or credit note stock movement details for invoice number') . ' ' . $FromTransNo . ' ' . _('from the database');
			if ($debug==1){
			    echo '<br>' . _('The SQL used to get this information that failed was') . "<br>$sql";
			}
			include('includes/footer.inc');
			exit;
		}

		if (DB_num_rows($result)>0){

			$FontSize = 10;
			$PageNumber = 1;

			include('includes/PDFTransPageHeader.inc');
			$FirstPage = False;

		        while ($myrow2=DB_fetch_array($result)){

				$DisplayPrice = number_format($myrow2['fxprice'],2);
				$DisplayQty = number_format($myrow2['quantity'],2);
				$DisplayNet = number_format($myrow2['fxnet'],2);

				if ($myrow2['discountpercent']==0){
					$DisplayDiscount ='';
				} else {
					$DisplayDiscount = number_format($myrow2['discountpercent']*100,2) . '%';
				}

				$LeftOvers = $pdf->addTextWrap($Left_Margin+3,$YPos,95,$FontSize,$myrow2['stockid']);
				$LeftOvers = $pdf->addTextWrap($Left_Margin+100,$YPos,245,$FontSize,$myrow2['description']);
				$LeftOvers = $pdf->addTextWrap($Left_Margin+353,$YPos,96,$FontSize,$DisplayPrice,'right');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+453,$YPos,96,$FontSize,$DisplayQty,'right');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+553,$YPos,35,$FontSize,$myrow2['units'],'centre');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+590,$YPos,50,$FontSize,$DisplayDiscount,'right');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+642,$YPos,120,$FontSize,$DisplayNet,'right');

				$YPos -= ($line_height);


				$lines=explode('\r\n',$Narrative);
				for ($i=0;$i<sizeOf($lines);$i++) {
				while (strlen($lines[$i])>1){
					if ($YPos-$line_height <= $Bottom_Margin){
						/* head up a new invoice/credit note page */
						/*draw the vertical column lines right to the bottom */
						PrintLinesToBottom ();
	   		        	include ('includes/PDFTransPageHeaderPortrait.inc');
			   		} //end if need a new page headed up
			   		/*increment a line down for the next line item */
			   		if (strlen($lines[$i])>1){
						$lines[$i] = $pdf->addTextWrap($Left_Margin+100,$YPos,245,$FontSize,stripslashes($lines[$i]));
					}
					$YPos -= ($line_height);
				}
				}
				if ($YPos <= $Bottom_Margin){

					/* head up a new invoice/credit note page */
					/*draw the vertical column lines right to the bottom */
					PrintLinesToBottom ();
					include ('includes/PDFTransPageHeader.inc');
				} //end if need a new page headed up


			} //end while there are line items to print out
		} /*end if there are stock movements to show on the invoice or credit note*/

		$YPos -= $line_height;

		/* check to see enough space left to print the 4 lines for the totals/footer */
		if (($YPos-$Bottom_Margin)<(2*$line_height)){

			PrintLinesToBottom ();

			include ('includes/PDFTransPageHeader.inc');

		}
		/*Print a column vertical line  with enough space for the footer*/
		/*draw the vertical column lines to 4 lines shy of the bottom
		to leave space for invoice footer info ie totals etc*/
		$pdf->line($Left_Margin+97, $TopOfColHeadings+12,$Left_Margin+97,$Bottom_Margin+(4*$line_height));

		/*Print a column vertical line */
		$pdf->line($Left_Margin+350, $TopOfColHeadings+12,$Left_Margin+350,$Bottom_Margin+(4*$line_height));

		/*Print a column vertical line */
		$pdf->line($Left_Margin+450, $TopOfColHeadings+12,$Left_Margin+450,$Bottom_Margin+(4*$line_height));

		/*Print a column vertical line */
		$pdf->line($Left_Margin+550, $TopOfColHeadings+12,$Left_Margin+550,$Bottom_Margin+(4*$line_height));

		/*Print a column vertical line */
		$pdf->line($Left_Margin+587, $TopOfColHeadings+12,$Left_Margin+587,$Bottom_Margin+(4*$line_height));

		$pdf->line($Left_Margin+640, $TopOfColHeadings+12,$Left_Margin+640,$Bottom_Margin+(4*$line_height));

		/*Rule off at bottom of the vertical lines */
		$pdf->line($Left_Margin, $Bottom_Margin+(4*$line_height),$Page_Width-$Right_Margin,$Bottom_Margin+(4*$line_height));

		/*Now print out the footer and totals */

		if ($InvOrCredit=='Invoice') {

		     $DisplaySubTot = number_format($myrow['ovamount'],2);
		     $DisplayFreight = number_format($myrow['ovfreight'],2);
		     $DisplayTax = number_format($myrow['ovgst'],2);
		     $DisplayTotal = number_format($myrow['ovfreight']+$myrow['ovgst']+$myrow['ovamount'],2);

		} else {

		     $DisplaySubTot = number_format(-$myrow['ovamount'],2);
		     $DisplayFreight = number_format(-$myrow['ovfreight'],2);
		     $DisplayTax = number_format(-$myrow['ovgst'],2);
		     $DisplayTotal = number_format(-$myrow['ovfreight']-$myrow['ovgst']-$myrow['ovamount'],2);
		}
	/*Print out the invoice text entered */
		$YPos = $Bottom_Margin+(3*$line_height);
	/* Print out the payment terms */

  		$pdf->addTextWrap($Left_Margin+5,$YPos+3,280,$FontSize,_('Payment Terms') . ': ' . $myrow['terms']);
//      $pdf->addText($Page_Width-$Right_Margin-392, $YPos - ($line_height*3)+22,$FontSize, _('Bank Code:***** Bank Account:*****'));
                        $FontSize=10;

		$FontSize =8;
		$LeftOvers = $pdf->addTextWrap($Left_Margin+5,$YPos-12,280,$FontSize,$myrow['invtext']);
		if (strlen($LeftOvers)>0){
			$LeftOvers = $pdf->addTextWrap($Left_Margin+5,$YPos-24,280,$FontSize,$LeftOvers);
			if (strlen($LeftOvers)>0){
				$LeftOvers = $pdf->addTextWrap($Left_Margin+5,$YPos-36,280,$FontSize,$LeftOvers);
				/*If there is some of the InvText leftover after 3 lines 200 wide then it is not printed :( */
			}
		}
		$FontSize = 10;

		$pdf->addText($Page_Width-$Right_Margin-220, $YPos+5,$FontSize, _('Sub Total'));
		$LeftOvers = $pdf->addTextWrap($Left_Margin+642,$YPos+5,120,$FontSize,$DisplaySubTot, 'right');

		$pdf->addText($Page_Width-$Right_Margin-220, $YPos-$line_height+5,$FontSize, _('Freight'));
		$LeftOvers = $pdf->addTextWrap($Left_Margin+642,$YPos-$line_height+5,120,$FontSize,$DisplayFreight, 'right');

		$pdf->addText($Page_Width-$Right_Margin-220, $YPos-(2*$line_height)+5,$FontSize, _('Tax'));
		$LeftOvers = $pdf->addTextWrap($Left_Margin+642,$YPos-(2*$line_height)+5,120, $FontSize,$DisplayTax, 'right');

		/*rule off for total */
		$pdf->line($Page_Width-$Right_Margin-222, $YPos-(2*$line_height),$Page_Width-$Right_Margin,$YPos-(2*$line_height));

		/*vertical to separate totals from comments and ROMALPA */
		$pdf->line($Page_Width-$Right_Margin-222, $YPos+$line_height,$Page_Width-$Right_Margin-222,$Bottom_Margin);

		$YPos+=10;
		if ($InvOrCredit=='Invoice'){
			$pdf->addText($Page_Width-$Right_Margin-220, $YPos - ($line_height*3)-6,$FontSize, _('TOTAL INVOICE'));
			$FontSize=9;
			$LeftOvers = $pdf->addTextWrap($Left_Margin+300,$YPos-4,245,$FontSize,$_SESSION['RomalpaClause']);
			while (strlen($LeftOvers)>0 AND $YPos > $Bottom_Margin){
				$YPos-=14;
				$LeftOvers = $pdf->addTextWrap($Left_Margin+300,$YPos,245,$FontSize,$LeftOvers);
			}
			/* Add Images for Visa / Mastercard / Paypal */
		if (file_exists('companies/' . $_SESSION['DatabaseName'] . '/payment.jpg')) {
			$pdf->addJpegFromFile('companies/' . $_SESSION['DatabaseName'] . '/payment.jpg',$Page_Width/2 -280,$YPos-20,0,40);
		}
			$pdf->addText($Page_Width-$Right_Margin-472, $YPos - ($line_height*3)+32,$FontSize, '');

			$FontSize=10;
		} else {
			$pdf->addText($Page_Width-$Right_Margin-220, $YPos-($line_height*3),$FontSize, _('TOTAL CREDIT'));
 		}
		$LeftOvers = $pdf->addTextWrap($Left_Margin+642,35,120, $FontSize,$DisplayTotal, 'right');
	    } /* end of check to see that there was an invoice record to print */

	    $FromTransNo++;
	} /* end loop to print invoices */

	$pdfcode = $pdf->output($_SESSION['reports_dir'] . '/Invoice.pdf', "F");
	$len = strlen($pdfcode);


// Start FPDI concatination to append PDF files conditionally to the invoice
// This part taken from FPDI example page
class concat_pdf extends FPDI {

	    var $files = array();

	    function setFiles($files) {
	        $this->files = $files;
	    }

	    function concat() {
	        foreach($this->files AS $file) {
	        	if ($file != 'pdf_append/none') {
	            	$pagecount = $this->setSourceFile($file);
	            	for ($i = 1; $i <= $pagecount; $i++) {
	                 	$tplidx = $this->ImportPage($i);
	                 	$s = $this->getTemplatesize($tplidx);
	                 	$this->AddPage($s['h'] > $s['w'] ? 'P' : 'L');
	                 	$this->useTemplate($tplidx);
	            	}
	        	}
	        }
	    }

	}

	$pdf =& new concat_pdf();
// Have to get the TransNo again, not sure what happens if we have a series of trans nos
if (isset($_GET['FromTransNo'])){
	$FromTransNo = trim($_GET['FromTransNo']);
} elseif (isset($_POST['FromTransNo'])){
	$FromTransNo = trim($_POST['FromTransNo']);
}
// Check its an Invoice type again, then select appendfile filename
//if ($InvOrCredit=='Invoice'){
			 //$sql = 'SELECT stockmoves.stockid, stockmaster.appendfile
				//	FROM stockmoves,
					//stockmaster
				//WHERE stockmoves.stockid = stockmaster.stockid
				//AND stockmoves.type=10
				//AND stockmoves.transno=' . $FromTransNo . '
				//AND stockmoves.show_on_inv_crds=1';
		//};

			 $sql = 'SELECT stockmoves.stockid, stockmaster.appendfile
					FROM stockmoves,
					stockmaster
				WHERE stockmoves.stockid = stockmaster.stockid
				AND stockmoves.type=10
				AND stockmoves.transno=' . $FromTransNo . '
				AND stockmoves.show_on_inv_crds=1';

$result=DB_query($sql,$db);
 // Loop the result set and add appendfile if the field is not 0 or none
while ($row=DB_fetch_array($result)){
    if ($row['appendfile'] !='0' AND $row['appendfile'] !=='none') {
		$pdf->setFiles(array($_SESSION['reports_dir'] . '/Invoice.pdf','companies/' . $_SESSION['DatabaseName'] . '/pdf_append/' . $row['appendfile'])); 
        $pdf->concat();
		$pdf->Output($_SESSION['CompanyRecord']['coyname'] . '_Invoice.pdf','I');
        exit;
	 // If EMAIL is selected, send the invoice via email, this is not appending pages yet though        
    } else if (isset($_GET['Email'])) {

        $pdf->setFiles(array($_SESSION['reports_dir'] . '/Invoice.pdf'));
        $pdf->concat();
		$pdfcode = $pdf->Output($_SESSION['CompanyRecord']['coyname'] . '_Invoice.pdf');    
    } else {
	 // If the appendfile field is empty and EMAIL is not selected, just print the invoice without any appended pages       
		$pdf->setFiles(array($_SESSION['reports_dir'] . '/Invoice.pdf'));
        $pdf->concat();
		$pdf->Output($_SESSION['CompanyRecord']['coyname'] . '_Invoice.pdf','D');
        exit;
    }
}
//End FPDI Concat

/*	if ($len <1020){
		include('includes/header.inc');
		echo '<p>' . _('There were no transactions to print in the range selected');
		include('includes/footer.inc');
		exit;
	}*/

	if (isset($_GET['Email'])){ //email the invoice to address supplied
		include('includes/header.inc');

		include ('includes/htmlMimeMail.php');

		$mail = new htmlMimeMail();
		$filename = $_SESSION['reports_dir'] . '/Invoice.pdf';
    	$fp = fopen( $_SESSION['reports_dir'] . '/Invoice.pdf','wb');
		fwrite ($fp, $pdfcode);
		fclose ($fp);

		$attachment = $mail->getFile($filename);
		$mail->setText(_('Please find attached') . ' ' . $InvOrCredit . ' ' . $_GET['FromTransNo'] );
		$mail->SetSubject($InvOrCredit . ' ' . $_GET['FromTransNo']);
		$mail->addAttachment($attachment, $filename, 'application/pdf');
		$mail->setFrom($_SESSION['CompanyRecord']['coyname'] . ' <' . $_SESSION['CompanyRecord']['email'] . '>');
		$result = $mail->send(array($_GET['Email']));

		unlink($filename); //delete the temporary file

		$title = _('Emailing') . ' ' .$InvOrCredit . ' ' . _('Number') . ' ' . $FromTransNo;
		include('includes/header.inc');
		echo "<p>$InvOrCredit " . _('number') . ' ' . $_GET['FromTransNo'] . ' ' . _('has been emailed to') . ' ' . $_GET['Email'];
		include('includes/footer.inc');
		exit;

	} else {
		header('Content-type: application/pdf');
		header('Content-Length: ' . $len);
		header('Content-Disposition: inline; filename=Customer_trans.pdf');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		$pdf->Stream();
	}

} else { /*The option to print PDF was not hit */

	$title=_('Select Invoices/Credit Notes To Print');
	include('includes/header.inc');

	if (!isset($FromTransNo) OR $FromTransNo=='') {


	/*if FromTransNo is not set then show a form to allow input of either a single invoice number or a range of invoices to be printed. Also get the last invoice number created to show the user where the current range is up to */
		echo "<form action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'><table class='table1'>";
                echo '<div class="centre"><p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Print') . '" alt="">' . ' ' . _('Print Invoices or Credit Notes (Landscape Mode)') . '</div>';
		echo '<tr><td>' . _('Print Invoices or Credit Notes') . '</td><td><select name=InvOrCredit>';
		if ($InvOrCredit=='Invoice' OR !isset($InvOrCredit)){

		   echo "<option selected VALUE='Invoice'>" . _('Invoices');
		   echo "<option VALUE='Credit'>" . _('Credit Notes');

		} else {

		   echo "<option selected VALUE='Credit'>" . _('Credit Notes');
		   echo "<option VALUE='Invoice'>" . _('Invoices');

		}

		echo '</select></td></tr>';

		echo '<tr><td>' . _('Print EDI Transactions') . '</td><td><select name=PrintEDI>';
		if ($InvOrCredit=='Invoice' OR !isset($InvOrCredit)){

		   echo "<option selected VALUE='No'>" . _('Do not Print PDF EDI Transactions');
		   echo "<option VALUE='Yes'>" . _('Print PDF EDI Transactions Too');

		} else {

		   echo "<option VALUE='No'>" . _('Do not Print PDF EDI Transactions');
		   echo "<option selected VALUE='Yes'>" . _('Print PDF EDI Transactions Too');

		}

		echo '</select></td></tr>';
		echo '<tr><td>' . _('Start invoice/credit note number to print') . '</td><td><input Type=text class=number max=6 size=7 name=FromTransNo></td></tr>';
		echo '<tr><td>' . _('End invoice/credit note number to print') . "</td><td><input Type=text class=number max=6 size=7 name='ToTransNo'></td></tr></table>";
		echo "<div class='centre'><input type=Submit Name='Print' Value='" . _('Print') . "'><p>";
		echo "<input type=Submit Name='PrintPDF' Value='" . _('Print PDF') . "'></div>";

		$sql = 'SELECT typeno FROM systypes WHERE typeid=10';

		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);

		echo '<div class="page_help_text"><b>' . _('The last invoice created was number') . ' ' . $myrow[0] . '</b><br>' . _('If only a single invoice is required') . ', ' . _('enter the invoice number to print in the Start transaction number to print field and leave the End transaction number to print field blank') . '. ' . _('Only use the end invoice to print field if you wish to print a sequential range of invoices') . '';

                $sql = 'SELECT typeno FROM systypes WHERE typeid=11';

                $result = DB_query($sql,$db);
                $myrow = DB_fetch_row($result);

                echo '<br><b>' . _('The last credit note created was number') . ' ' . $myrow[0] . '</b><br>' . _('A sequential range can be printed using the same method as for invoices above') . '. ' . _('A single credit note can be printed by only entering a start transaction number') . '</DIV';

	} else {

		while ($FromTransNo <= $_POST['ToTransNo']){

	/*retrieve the invoice details from the database to print
	notice that salesorder record must be present to print the invoice purging of sales orders will
	nobble the invoice reprints */

			if ($InvOrCredit=='Invoice') {

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
				AND debtortrans.transno=" . $FromTransNo . "
				AND debtortrans.shipvia=shippers.shipper_id
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
				AND custbranch.salesman=salesman.salesmancode";
			} else {

			   $sql = 'SELECT debtortrans.trandate,
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
				AND debtortrans.transno=' . $FromTransNo . '
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
				AND custbranch.salesman=salesman.salesmancode';

			}

			$result=DB_query($sql,$db);
			if (DB_num_rows($result)==0 OR DB_error_no($db)!=0) {
				echo '<p>' . _('There was a problem retrieving the invoice or credit note details for note number') . ' ' . $InvoiceToPrint . ' ' . _('from the database') . '. ' . _('To print an invoice, the sales order record, the customer transaction record and the branch record for the customer must not have been purged') . '. ' . _('To print a credit note only requires the customer, transaction, salesman and branch records be available');
				if ($debug==1){
					echo _('The SQL used to get this information that failed was') . "<br>$sql";
				}
				break;
				include('includes/footer.inc');
				exit;
			} elseif (DB_num_rows($result)==1){

				$myrow = DB_fetch_array($result);
	/* Then there's an invoice (or credit note) to print. So print out the invoice header and GST Number from the company record */
				if (count($_SESSION['AllowedPageSecurityTokens'])==1 AND in_array(1, $_SESSION['AllowedPageSecurityTokens']) AND $myrow['debtorno'] != $_SESSION['CustomerID']){
					echo '<p><font color=RED size=4>' . _('This transaction is addressed to another customer and cannot be displayed for privacy reasons') . '. ' . _('Please select only transactions relevant to your company');
					exit;
				}

				$ExchRate = $myrow['rate'];
				$PageNumber = 1;

				echo "<table class='table1'><tr><td VALIGN=TOP WIDTH=10%><img src='" . $_SESSION['LogoFile'] . "'></td><td bgcolor='#BBBBBB'><b>";

				if ($InvOrCredit=='Invoice') {
				   echo '<font size=4>' . _('TAX INVOICE') . ' ';
				} else {
				   echo '<font color=RED size=4>' . _('TAX CREDIT NOTE') . ' ';
				}
				echo '</b>' . _('Number') . ' ' . $FromTransNo . '</font><br><font size=1>' . _('Tax Authority Ref') . '. ' . $_SESSION['CompanyRecord']['gstno'] . '</td></tr></table>';

	/*Now print out the logo and company name and address */
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

				echo '</td><td WIDTH=50% align=right>';

	/*Now the customer charged to details in a sub table within a cell of the main table*/

				echo "<table class='table1'><tr><td align=left bgcolor='#BBBBBB'><b>" . _('Charge To') . ":</b></td></tr><tr><td bgcolor='#EEEEEE'>";
				echo $myrow['name'] . '<br>' . $myrow['address1'] . '<br>' . $myrow['address2'] . '<br>' . $myrow['address3'] . '<br>' . $myrow['address4'] . '<br>' . $myrow['address5'] . '<br>' . $myrow['address6'];
				echo '</td></tr></table>';
				/*end of the small table showing charge to account details */
				echo _('Page') . ': ' . $PageNumber;
				echo '</td></tr></table>';
				/*end of the main table showing the company name and charge to details */

				if ($InvOrCredit=='Invoice') {

				   echo "<table class='table1'>
				   			<tr>
				   				<td align=left bgcolor='#BBBBBB'><b>" . _('Charge Branch') . ":</b></td>
								<td align=left bgcolor='#BBBBBB'><b>" . _('Delivered To') . ":</b></td>
							</tr>";
				   echo "<tr>
				   		<td bgcolor='#EEEEEE'>" .$myrow['brname'] . '<br>' . $myrow['braddress1'] . '<br>' . $myrow['braddress2'] . '<br>' . $myrow['braddress3'] . '<br>' . $myrow['braddress4'] . '<br>' . $myrow['braddress5'] . '<br>' . $myrow['braddress6'] . '</td>';

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
							<td bgcolor='#EEEEEE'>" .$myrow['orderno'] . "</td>
							<td bgcolor='#EEEEEE'>" . ConvertSQLDate($myrow['orddate']) . "</td>
							<td bgcolor='#EEEEEE'>" . ConvertSQLDate($myrow['trandate']) . "</td>
							<td bgcolor='#EEEEEE'>" . $myrow['salesmanname'] . "</td>
							<td bgcolor='#EEEEEE'>" . $myrow['shippername'] . "</td>
							<td bgcolor='#EEEEEE'>" . $myrow['consignment'] . "</td>
						</tr>
					</table>";

				   $sql ="SELECT stockmoves.stockid,
				   		stockmaster.description,
						-stockmoves.qty as quantity,
						stockmoves.discountpercent,
						((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . '* -stockmoves.qty) AS fxnet,
						(stockmoves.price * ' . $ExchRate . ') AS fxprice,
						stockmoves.narrative,
						stockmaster.units
					FROM stockmoves,
						stockmaster
					WHERE stockmoves.stockid = stockmaster.stockid
					AND stockmoves.type=10
					AND stockmoves.transno=' . $FromTransNo . '
					AND stockmoves.show_on_inv_crds=1';

				} else { /* then its a credit note */

				   echo "<table WIDTH=50%><tr>
				   		<td align=left bgcolor='#BBBBBB'><b>" . _('Branch') . ":</b></td>
						</tr>";
				   echo "<tr>
				   		<td bgcolor='#EEEEEE'>" .$myrow['brname'] . '<br>' . $myrow['braddress1'] . '<br>' . $myrow['braddress2'] . '<br>' . $myrow['braddress3'] . '<br>' . $myrow['braddress4'] . '<br>' . $myrow['braddress5'] . '<br>' . $myrow['braddress6'] . '</td>
					</tr></table>';
				   echo "<hr><table class='table1'><tr>
				   		<td align=left bgcolor='#BBBBBB'><b>" . _('Date') . "</b></td>
						<td align=left bgcolor='#BBBBBB'><b>" . _('Sales Person') . "</font></b></td>
					</tr>";
				   echo "<tr>
				   		<td bgcolor='#EEEEEE'>" . ConvertSQLDate($myrow['trandate']) . "</td>
						<td bgcolor='#EEEEEE'>" . $myrow['salesmanname'] . '</td>
					</tr></table>';

				   $sql ='SELECT stockmoves.stockid,
				   		stockmaster.description,
						stockmoves.qty as quantity,
						stockmoves.discountpercent, ((1 - stockmoves.discountpercent) * stockmoves.price * ' . $ExchRate . ' * stockmoves.qty) AS fxnet,
						(stockmoves.price * ' . $ExchRate . ') AS fxprice,
						stockmaster.units
					FROM stockmoves,
						stockmaster
					WHERE stockmoves.stockid = stockmaster.stockid
					AND stockmoves.type=11
					AND stockmoves.transno=' . $FromTransNo . '
					AND stockmoves.show_on_inv_crds=1';
				}

				echo '<hr>';
				echo '<div class="centre"><font size=2>' . _('All amounts stated in') . ' ' . $myrow['currcode'] . '</font></div>';

				$result=DB_query($sql,$db);
				if (DB_error_no($db)!=0) {
					echo '<br>' . _('There was a problem retrieving the invoice or credit note stock movement details for invoice number') . ' ' . $FromTransNo . ' ' . _('from the database');
					if ($debug==1){
						 echo '<br>' . _('The SQL used to get this information that failed was') . "<br>$sql";
					}
					exit;
				}

				if (DB_num_rows($result)>0){
					echo "<table class='table1'>
						<tr><th>" . _('Item Code') . "</th>
						<th>" . _('Item Description') . "</th>
						<th>" . _('Quantity') . "</th>
						<th>" . _('Unit') . "</th>
						<th>" . _('Price') . "</th>
						<th>" . _('Discount') . "</th>
						<th>" . _('Net') . '</th></tr>';

					$LineCounter =17;
					$k=0;	//row colour counter

					while ($myrow2=DB_fetch_array($result)){

					      if ($k==1){
						  $RowStarter = '<tr class="EvenTableRows">';
						  $k=0;
					      } else {
						  $RowStarter = '<tr class="OddTableRows">';
						  $k=1;
					      }

					      echo $RowStarter;

					      $DisplayPrice = number_format($myrow2['fxprice'],2);
					      $DisplayQty = number_format($myrow2['quantity'],2);
					      $DisplayNet = number_format($myrow2['fxnet'],2);

					      if ($myrow2['discountpercent']==0){
						   $DisplayDiscount ='';
					      } else {
						   $DisplayDiscount = number_format($myrow2['discountpercent']*100,2) . '%';
					      }

					      printf ('<td>%s</td>
					      		<td>%s</td>
							<td align=right>%s</td>
							<td align=right>%s</td>
							<td align=right>%s</td>
							<td align=right>%s</td>
							<td align=right>%s</td>
							</tr>',
							$myrow2['stockid'],
							$myrow2['description'],
							$DisplayQty,
							$myrow2['units'],
							$DisplayPrice,
							$DisplayDiscount,
							$DisplayNet);

					      if (strlen($myrow2['narrative'])>1){
					      		echo $RowStarter . '<td></td><td colspan=6>' . $myrow2['narrative'] . '</td></tr>';
							$LineCounter++;
					      }

					      $LineCounter++;

					      if ($LineCounter == ($_SESSION['PageLength'] - 2)){

						/* head up a new invoice/credit note page */

						   $PageNumber++;
						   echo "</table><table class='table1'><tr><td VALIGN=TOp><img src='" . $_SESSION['LogoFile'] . "'></td><td bgcolor='#BBBBBB'><b>";

						   if ($InvOrCredit=='Invoice') {
							    echo '<font size=4>' . _('TAX INVOICE') . ' ';
						   } else {
							    echo '<font color=RED size=4>' . _('TAX CREDIT NOTE') . ' ';
						   }
						   echo '</b>' . _('Number') . ' ' . $FromTransNo . '</font><br><font size=1>' . _('GST Number') . ' - ' . $_SESSION['CompanyRecord']['gstno'] . '</td></tr><class=table1';

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
						    echo '</td><td align=right>' . _('Page') . ": $PageNumber</td></tr></table>";
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
				$LinesRequiredForText = floor(strlen($myrow['invtext'])/140);

				if ($LineCounter >= ($_SESSION['PageLength'] - 8 - $LinesRequiredForText)){

					/* head up a new invoice/credit note page */

					$PageNumber++;
					echo "<table class='table1'><tr><td VALIGN=TOp><img src='" . $_SESSION['LogoFile'] . "'></td><td bgcolor='#BBBBBB'><b>";

					if ($InvOrCredit=='Invoice') {
					      echo '<font size=4>' . _('TAX INVOICE') .' ';
					} else {
					      echo '<font color=RED size=4>' . _('TAX CREDIT NOTE') . ' ';
					}
					echo '</b>' . _('Number') . ' ' . $FromTransNo . '</font><br><font size=1>' . _('GST Number') . ' - ' . $_SESSION['CompanyRecord']['gstno'] . '</td></tr><table>';

	/*Print out the logo and company name and address */
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
					echo '</td><td align=right>' . _('Page') . ": $PageNumber</td></tr></table>";
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

	/*Space out the footer to the bottom of the page */

				echo '<br><br>' . $myrow['invtext'];

				$LineCounter=$LineCounter+2+$LinesRequiredForText;
				while ($LineCounter < ($_SESSION['PageLength'] -6)){
					echo '<br>';
					$LineCounter++;
				}

	/*Now print out the footer and totals */

				if ($InvOrCredit=='Invoice') {

				   $DisplaySubTot = number_format($myrow['ovamount'],2);
				   $DisplayFreight = number_format($myrow['ovfreight'],2);
				   $DisplayTax = number_format($myrow['ovgst'],2);
				   $DisplayTotal = number_format($myrow['ovfreight']+$myrow['ovgst']+$myrow['ovamount'],2);
				} else {
				   $DisplaySubTot = number_format(-$myrow['ovamount'],2);
				   $DisplayFreight = number_format(-$myrow['ovfreight'],2);
				   $DisplayTax = number_format(-$myrow['ovgst'],2);
				   $DisplayTotal = number_format(-$myrow['ovfreight']-$myrow['ovgst']-$myrow['ovamount'],2);
				}
	/*Print out the invoice text entered */
				echo '<table class=table1><tr>
					<td align=right>' . _('Sub Total') . "</td>
					<td align=right bgcolor='#EEEEEE' WIDTH=15%>$DisplaySubTot</td></tr>";
				echo '<tr><td align=right>' . _('Freight') . "</td>
					<td align=right bgcolor='#EEEEEE'>$DisplayFreight</td></tr>";
				echo '<tr><td align=right>' . _('Tax') . "</td>
					<td align=right bgcolor='#EEEEEE'>$DisplayTax</td></tr>";
				if ($InvOrCredit=='Invoice'){
				     echo '<tr><td Align=RIGHT><b>' . _('TOTAL INVOICE') . "</b></td>
				     	<td align=right bgcolor='#EEEEEE'><U><b>$DisplayTotal</b></U></td></tr>";
				} else {
				     echo '<tr><td Align=RIGHT><font color=RED><b>' . _('TOTAL CREDIT') . "</b></font></td>
				     		<td align=right bgcolor='#EEEEEE'><font color=RED><U><b>$DisplayTotal</b></U></font></td></tr>";
				}
				echo '</table>';
			} /* end of check to see that there was an invoice record to print */
			$FromTransNo++;
		} /* end loop to print invoices */
	} /*end of if FromTransNo exists */
	include('includes/footer.inc');

} /*end of else not PrintPDF */



function PrintLinesToBottom () {

	global $pdf;
	global $PageNumber;
	global $TopOfColHeadings;
	global $Left_Margin;
	global $Bottom_Margin;
	global $line_height;


/*draw the vertical column lines right to the bottom */
	$pdf->line($Left_Margin+97, $TopOfColHeadings+12,$Left_Margin+97,$Bottom_Margin);

	/*Print a column vertical line */
	$pdf->line($Left_Margin+350, $TopOfColHeadings+12,$Left_Margin+350,$Bottom_Margin);

	/*Print a column vertical line */
	$pdf->line($Left_Margin+450, $TopOfColHeadings+12,$Left_Margin+450,$Bottom_Margin);

	/*Print a column vertical line */
	$pdf->line($Left_Margin+550, $TopOfColHeadings+12,$Left_Margin+550,$Bottom_Margin);

	/*Print a column vertical line */
	$pdf->line($Left_Margin+587, $TopOfColHeadings+12,$Left_Margin+587,$Bottom_Margin);

	$pdf->line($Left_Margin+640, $TopOfColHeadings+12,$Left_Margin+640,$Bottom_Margin);

//	$pdf->newPage();
	$PageNumber++;

}

?>

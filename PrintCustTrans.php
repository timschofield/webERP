<?php
/* $Revision: 1.7 $ */
$PageSecurity = 1;

if (!function_exists('_')){
	function _($text){
		return ($text);
	}
}

if (isset($_GET["FromTransNo"])){
	$FromTransNo = $_GET["FromTransNo"];
} elseif (isset($_POST["FromTransNo"])){
	$FromTransNo = $_POST["FromTransNo"];
}

if (!isset($FromTransNo)  || $FromTransNo=="") {
	$title=_('Select Invoices/Credit Notes To Print');
}

if (isset($_GET["InvOrCredit"])){
	$InvOrCredit = $_GET["InvOrCredit"];
} elseif (isset($_POST["InvOrCredit"])){
	$InvOrCredit = $_POST["InvOrCredit"];
}
if (isset($_GET["PrintPDF"])){
	$PrintPDF = $_GET["PrintPDF"];
} elseif (isset($_POST["PrintPDF"])){
	$PrintPDF = $_POST["PrintPDF"];
}

include("includes/SQL_CommonFunctions.inc");
include("includes/DateFunctions.inc");
include ("includes/class.pdf.php");
include ("includes/htmlMimeMail.php");


if (isset($_GET["FromTransNo"])){
	$FromTransNo = $_GET["FromTransNo"];
} elseif (isset($_POST["FromTransNo"])){
	$FromTransNo = $_POST["FromTransNo"];
} else {
	$FromTransNo ="";
}

If (!isset($_POST['ToTransNo']) OR $_POST['ToTransNo']==""){
	      $_POST['ToTransNo'] = $FromTransNo;
}
$FirstTrans = $FromTransNo; /*Need to start a new page only on subsequent transactions */

If (isset($PrintPDF) AND $PrintPDF!="" AND isset($FromTransNo) AND isset($InvOrCredit) AND $FromTransNo!=""){

	include("config.php");
	include("includes/ConnectDB.inc");

	if (isset($SessionSavePath)){
		session_save_path($SessionSavePath);
    	}

	session_start();

/*check security - $PageSecurity set in files where this script is included from */
	if (! in_array($PageSecurity,$SecurityGroups[$_SESSION["AccessLevel"]]) OR !isset($PageSecurity)){
		$title = _('Access Denied Error');
		include ('includes/header.inc');
		echo "<BR><BR><BR><BR><BR><BR><BR><CENTER><FONT COLOR=RED SIZE=4><B>" .
		_('The security settings on your account do not permit you to access this function') . "</B></FONT>";
		include('includes/footer.inc');
		exit;
	}

	/*This invoice is hard coded for A4 Landscape invoices or credit notes  so can't use PDFStarter.inc*/
	$Page_Width=842;
	$Page_Height=595;
	$Top_Margin=30;
	$Bottom_Margin=30;
	$Left_Margin=40;
	$Right_Margin=30;

	$PageSize = array(0,0,$Page_Width,$Page_Height);
	$pdf = & new Cpdf($PageSize);
	$pdf->selectFont('./fonts/Helvetica.afm');
	$pdf->addinfo('Author',"WEB-ERP " . $Version);
	$pdf->addinfo('Creator',"WEB-ERP http://weberp.sourceforge.net - R&OS PHP-PDF http://www.ros.co.nz");


	if ($InvOrCredit=="Invoice"){
		$pdf->addinfo('Title',_('Sales Invoice'));
		$pdf->addinfo('Subject',_('Invoices from') . ' ' . $FromTransNo . ' ' . _('to') . ' ' . $_POST["ToTransNo"]);
	} else {
		$pdf->addinfo('Title',_('Sales Credit Note'));
		$pdf->addinfo('Subject',_('Credit Notes from') . ' ' . $FromTransNo . ' ' . _('to') . ' ' . $_POST["ToTransNo"]);
	}

	$line_height=16;

	/*We have a range of invoices to print so get an array of all the company information */
	$CompanyRecord = ReadInCompanyRecord ($db);
	if ($CompanyRecord==0){
	/*CompanyRecord will be 0 if the company information could not be retrieved */
	     exit;
	}


	while ($FromTransNo <= $_POST['ToTransNo']){

	/*retrieve the invoice details from the database to print
	notice that salesorder record must be present to print the invoice purging of sales orders will
	nobble the invoice reprints */

	   if ($InvOrCredit=="Invoice") {
		$sql = "SELECT DebtorTrans.TranDate,
				DebtorTrans.OvAmount,
				DebtorTrans.OvDiscount,
				DebtorTrans.OvFreight,
				DebtorTrans.OvGST,
				DebtorTrans.Rate,
				DebtorTrans.InvText,
				DebtorTrans.Consignment,
				DebtorsMaster.Name,
				DebtorsMaster.Address1,
				DebtorsMaster.Address2,
				DebtorsMaster.Address3,
				DebtorsMaster.Address4,
				DebtorsMaster.CurrCode,
				DebtorsMaster.InvAddrBranch,
				PaymentTerms.Terms,
				SalesOrders.DeliverTo,
				SalesOrders.DelAdd1,
				SalesOrders.DelAdd2,
				SalesOrders.DelAdd3,
				SalesOrders.DelAdd4,
				SalesOrders.CustomerRef,
				SalesOrders.OrderNo,
				SalesOrders.OrdDate,
				Locations.LocationName,
				Shippers.ShipperName,
				CustBranch.BrName,
				CustBranch.BrAddress1,
				CustBranch.BrAddress2,
				CustBranch.BrAddress3,
				CustBranch.BrAddress4,
				CustBranch.BrPostAddr1,
				CustBranch.BrPostAddr2,
				CustBranch.BrPostAddr3,
				CustBranch.BrPostAddr4,
				Salesman.SalesmanName,
				DebtorTrans.DebtorNo,
				DebtorTrans.BranchCode
			FROM DebtorTrans,
				DebtorsMaster,
				CustBranch,
				SalesOrders,
				Shippers,
				Salesman,
				Locations,
				PaymentTerms
			WHERE DebtorTrans.Order_ = SalesOrders.OrderNo
			AND DebtorTrans.Type=10
			AND DebtorTrans.TransNo=" . $FromTransNo . "
			AND DebtorTrans.ShipVia=Shippers.Shipper_ID
			AND DebtorTrans.DebtorNo=DebtorsMaster.DebtorNo
			AND DebtorsMaster.PaymentTerms=PaymentTerms.TermsIndicator
			AND DebtorTrans.DebtorNo=CustBranch.DebtorNo
			AND DebtorTrans.BranchCode=CustBranch.BranchCode
			AND CustBranch.Salesman=Salesman.SalesmanCode
			AND SalesOrders.FromStkLoc=Locations.LocCode";

		if ($_POST['PrintEDI']=='No'){
			$sql = $sql . " AND DebtorsMaster.EDIInvoices=0";
		}
	   } else {

		$sql = "SELECT DebtorTrans.TranDate,
				DebtorTrans.OvAmount,
				DebtorTrans.OvDiscount,
				DebtorTrans.OvFreight,
				DebtorTrans.OvGST,
				DebtorTrans.Rate,
				DebtorTrans.InvText,
				DebtorsMaster.InvAddrBranch,
				DebtorsMaster.Name,
				DebtorsMaster.Address1,
				DebtorsMaster.Address2,
				DebtorsMaster.Address3,
				DebtorsMaster.Address4,
				DebtorsMaster.CurrCode,
				CustBranch.BrName,
				CustBranch.BrAddress1,
				CustBranch.BrAddress2,
				CustBranch.BrAddress3,
				CustBranch.BrAddress4,
				CustBranch.BrPostAddr1,
				CustBranch.BrPostAddr2,
				CustBranch.BrPostAddr3,
				CustBranch.BrPostAddr4,
				Salesman.SalesmanName,
				DebtorTrans.DebtorNo,
				DebtorTrans.BranchCode,
				PaymentTerms.Terms
			FROM DebtorTrans,
				DebtorsMaster,
				CustBranch,
				Salesman,
				PaymentTerms
			WHERE DebtorTrans.Type=11
			AND DebtorsMaster.PaymentTerms = PaymentTerms.TermsIndicator
			AND DebtorTrans.TransNo=" . $FromTransNo ."
			AND DebtorTrans.DebtorNo=DebtorsMaster.DebtorNo
			AND DebtorTrans.DebtorNo=CustBranch.DebtorNo
			AND DebtorTrans.BranchCode=CustBranch.BranchCode
			AND CustBranch.Salesman=Salesman.SalesmanCode";

		if ($_POST['PrintEDI']=='No'){
			$sql = $sql . " AND DebtorsMaster.EDIInvoices=0";
		}
	   }
	   $result=DB_query($sql,$db);

	   if (DB_error_no($db)!=0) {

		$title = _('Transaction Print Error Report');
		include ("includes/header.inc");

		echo "<BR>" . _('There was a problem retrieving the invoice (or credit note) details for invoice/credit note number $InvoiceToPrint from the database. To print an <B>invoice</B> the sales order record, the customer transaction record and the branch record for the customer must not have been purged. To print a credit note only requires the customer, transaction, salesman and branch records be available');
		if ($debug==1){
		    echo _('The SQL used to get this information (that failed) was:') . "<BR>$sql";
		}
		break;
		include ("includes/footer.inc");
		exit;
	   }
	   if (DB_num_rows($result)==1){
		$myrow = DB_fetch_array($result);

		$ExchRate = $myrow["Rate"];

		if ($InvOrCredit=="Invoice"){

			 $sql = "SELECT StockMoves.StockID,
					StockMaster.Description,
					-StockMoves.Qty AS Quantity,
					StockMoves.DiscountPercent,
					((1 - StockMoves.DiscountPercent) * StockMoves.Price * " . $ExchRate . "* -StockMoves.Qty) AS FxNet,
					(StockMoves.Price * " . $ExchRate . ") AS FxPrice,
					StockMoves.Narrative,
					StockMaster.Units
				FROM StockMoves,
					StockMaster
				WHERE StockMoves.StockID = StockMaster.StockID
				AND StockMoves.Type=10
				AND StockMoves.TransNo=" . $FromTransNo . "
				AND StockMoves.Show_On_Inv_Crds=1";
		} else {
		/* only credit notes to be retrieved */
			 $sql = "SELECT StockMoves.StockID,
			 		StockMaster.Description,
					StockMoves.Qty AS Quantity,
					StockMoves.DiscountPercent,
					((1 - StockMoves.DiscountPercent) * StockMoves.Price * " . $ExchRate . " * StockMoves.Qty) AS FxNet,
					(StockMoves.Price * " . $ExchRate . ") AS FxPrice,
					StockMoves.Narrative,
					StockMaster.Units
				FROM StockMoves,
					StockMaster
				WHERE StockMoves.StockID = StockMaster.StockID
				AND StockMoves.Type=11
				AND StockMoves.TransNo=" . $FromTransNo . "
				AND StockMoves.Show_On_Inv_Crds=1";
		}

		$result=DB_query($sql,$db);
		if (DB_error_no($db)!=0) {
			$title = _("Transaction Print Error Report");
			include ("includes/header.inc");
			echo "<BR>" . _('There was a problem retrieving the invoice or credit note stock movement details for invoice number') . ' ' . $FromTransNo . ' ' . _('from the database');
			if ($debug==1){
			    echo "<BR>" . _('The SQL used to get this information (that failed) was:') . "<BR>$sql";
			}
			include("includes/footer.inc");
			exit;
		}

		if (DB_num_rows($result)>0){

			$FontSize = 10;
			$PageNumber = 1;

			if ($FromTransNo > $FirstTrans){ /* only initiate a new page if its not the first */
  				$pdf->newPage();
			}
		        include("includes/PDFTransPageHeader.inc");

		        while ($myrow2=DB_fetch_array($result)){

				$DisplayPrice = number_format($myrow2["FxPrice"],2);
				$DisplayQty = number_format($myrow2["Quantity"],2);
				$DisplayNet = number_format($myrow2["FxNet"],2);

				if ($myrow2["DiscountPercent"]==0){
					$DisplayDiscount ="";
				} else {
					$DisplayDiscount = number_format($myrow2["DiscountPercent"]*100,2) . "%";
				}

				$LeftOvers = $pdf->addTextWrap($Left_Margin+3,$YPos,95,$FontSize,$myrow2["StockID"]);
				$LeftOvers = $pdf->addTextWrap($Left_Margin+100,$YPos,245,$FontSize,$myrow2["Description"]);
				$LeftOvers = $pdf->addTextWrap($Left_Margin+353,$YPos,96,$FontSize,$DisplayPrice,'right');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+453,$YPos,96,$FontSize,$DisplayQty,'right');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+553,$YPos,35,$FontSize,$myrow2["Units"],'centre');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+590,$YPos,50,$FontSize,$DisplayDiscount,'right');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+642,$YPos,120,$FontSize,$DisplayNet,'right');

				$YPos -= ($line_height);

				$Narrative = $myrow2['Narrative'];
				do {
					if ($YPos-$line_height <= $Bottom_Margin){
						/* head up a new invoice/credit note page */
						/*draw the vertical column lines right to the bottom */
						PrintLinesToBottom ();
	   		        		include ("includes/PDFTransPageHeader.inc");
			   		} //end if need a new page headed up
			   		/*increment a line down for the next line item */
			   		if (strlen($Narrative)>1){
						$Narrative = $pdf->addTextWrap($Left_Margin+100,$YPos,245,$FontSize,$Narrative);
					}
					$YPos -= ($line_height);
				} while (strlen($Narrative)>1);


			} //end while there are line items to print out
		} /*end if there are stock movements to show on the invoice or credit note*/

		$YPos -= $line_height;

		/* check to see enough space left to print the 4 lines for the totals/footer */
		if (($YPos-$Bottom_Margin)<(4*$line_height)){

			PrintLinesToBottom ();
			include ("includes/PDFTransPageHeader.inc");

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

		if ($InvOrCredit=="Invoice") {

		     $DisplaySubTot = number_format($myrow["OvAmount"],2);
		     $DisplayFreight = number_format($myrow["OvFreight"],2);
		     $DisplayTax = number_format($myrow["OvGST"],2);
		     $DisplayTotal = number_format($myrow["OvFreight"]+$myrow["OvGST"]+$myrow["OvAmount"],2);

		} else {

		     $DisplaySubTot = number_format(-$myrow["OvAmount"],2);
		     $DisplayFreight = number_format(-$myrow["OvFreight"],2);
		     $DisplayTax = number_format(-$myrow["OvGST"],2);
		     $DisplayTotal = number_format(-$myrow["OvFreight"]-$myrow["OvGST"]-$myrow["OvAmount"],2);
		}
	/*Print out the invoice text entered */
		$YPos = $Bottom_Margin+(3*$line_height);
	/* Print out the payment terms */

  		$pdf->addTextWrap($Left_Margin+5,$YPos+3,280,$FontSize,_('Payment Terms:') . ' ' . $myrow['Terms']);

		$FontSize =8;
		$LeftOvers = $pdf->addTextWrap($Left_Margin+5,$YPos-12,280,$FontSize,$myrow["InvText"]);
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

		$pdf->addText($Page_Width-$Right_Margin-220, $YPos-(2*$line_height)+5,$FontSize, "Tax");
		$LeftOvers = $pdf->addTextWrap($Left_Margin+642,$YPos-(2*$line_height)+5,120, $FontSize,$DisplayTax, 'right');

		/*rule off for total */
		$pdf->line($Page_Width-$Right_Margin-222, $YPos-(2*$line_height),$Page_Width-$Right_Margin,$YPos-(2*$line_height));

		/*vertical to seperate totals from comments and ROMALPA */
		$pdf->line($Page_Width-$Right_Margin-222, $YPos+$line_height,$Page_Width-$Right_Margin-222,$Bottom_Margin);

		$YPos+=10;
		if ($InvOrCredit=="Invoice"){
			$pdf->addText($Page_Width-$Right_Margin-220, $YPos - ($line_height*3)-6,$FontSize, _('TOTAL INVOICE'));
			$FontSize=6;
			$LeftOvers = $pdf->addTextWrap($Left_Margin+300,$YPos,245,$FontSize,$RomalpaClause);
			while (strlen($LeftOvers)>0 AND $YPos > $Bottom_Margin){
				$YPos -=7;
				$LeftOvers = $pdf->addTextWrap($Left_Margin+300,$YPos,245,$FontSize,$LeftOvers);
			}
			$FontSize=10;
		} else {
			$pdf->addText($Page_Width-$Right_Margin-220, $YPos-($line_height*3),$FontSize, _('TOTAL CREDIT'));
 		}
		$LeftOvers = $pdf->addTextWrap($Left_Margin+642,35,120, $FontSize,$DisplayTotal, 'right');
	    } /* end of check to see that there was an invoice record to print */

	    $FromTransNo++;
	} /* end loop to print invoices */


	$pdfcode = $pdf->output();
	$len = strlen($pdfcode);

	if ($len <1020){
		include("includes/header.inc");
		echo "<P>" . _('There were no transactions to print in the range selected');
		include("includes/footer.inc");
		exit;
	}

	if (isset($_GET['Email'])){ //email the invoice to address supplied

		$mail = new htmlMimeMail();
		$filename = $reports_dir . "/" . $InvOrCredit . $FromTransNo . ".pdf";
		$fp = fopen($filename, "wb");
		fwrite ($fp, $pdfcode);
		fclose ($fp);

		$attachment = $mail->getFile($filename);
		$mail->setText(_('Please find attached') . ' ' . $InvOrCredit . " " . $FromTransNo );
		$mail->SetSubject($InvOrCredit . " " . $FromTransNo);
		$mail->addAttachment($attachment, $filename, 'application/pdf');
		$mail->setFrom($CompanyName . "<" . $CompanyRecord['Email'] . ">");
		$result = $mail->send(array($_GET['Email']));

		unlink($filename); //delete the temporary file

		$title = _('Emailing') . ' ' .$InvOrCredit . ' ' . _('Number') . ' ' . $FromTransNo;
		include("includes/header.inc");
		echo "<P>$InvOrCredit " . _('number') . $FromTransNo . ' ' . _('has been emailed to') . ' ' . $_GET['Email'];
		include("includes/footer.inc");
		exit;

	} else {
		header("Content-type: application/pdf");
		header("Content-Length: " . $len);
		header("Content-Disposition: inline; filename=Customer_trans.pdf");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Pragma: public");

		$pdf->Stream();
	}

} else { /*The option to print PDF was not hit */

	  include("includes/session.inc");
	  include("includes/header.inc");

	  if (!isset($FromTransNo) OR $FromTransNo=="") {


	/*if FromTransNo is not set then show a form to allow input of either a single invoice number or a range of invoices to be printed. Also get the last invoice number created to show the user where the current range is up to */

		echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD='POST'><CENTER><TABLE>";

		echo "<TR><TD>Print Invoices or Credit Notes</TD><TD><SELECT name=InvOrCredit>";
		if ($InvOrCredit=="Invoice" OR !isset($InvOrCredit)){

		   echo "<OPTION SELECTED VALUE='Invoice'>Invoices";
		   echo "<OPTION VALUE='Credit'>Credit Notes";

		} else {

		   echo "<OPTION SELECTED VALUE='CreditNote'>Credit Notes";
		   echo "<OPTION VALUE='Invoice'>Invoices";

		}

		echo "</SELECT></TD></TR>";

		echo "<TR><TD>Print EDI Transactions</TD><TD><SELECT name=PrintEDI>";
		if ($InvOrCredit=="Invoice" OR !isset($InvOrCredit)){

		   echo "<OPTION SELECTED VALUE='No'>No Don't Print PDF EDI Transactions";
		   echo "<OPTION VALUE='Yes'>Print PDF EDI Transactions Too";

		} else {

		   echo "<OPTION VALUE='No'>No Don't Print PDF EDI Transactions";
		   echo "<OPTION SELECTED VALUE='Yes'>Print PDF EDI Transactions Too";

		}

		echo "</SELECT></TD></TR>";
		echo "<TR><TD>Start invoice/credit note number to print</TD><TD><input Type=text max=6 size=7 name=FromTransNo></TD></TR>";
		echo "<TR><TD>End invoice/credit note number to print</TD><TD><input Type=text max=6 size=7 name='ToTransNo'></TD></TR></TABLE></CENTER>";
		echo "<CENTER><INPUT TYPE=Submit Name='Print' Value='Print'><P>";
		echo "<INPUT TYPE=Submit Name='PrintPDF' Value='Print PDF'></CENTER>";

		$sql = "SELECT TypeNo FROM SysTypes WHERE TypeID=10";

		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);

		echo "<P>The last invoice created was number " . $myrow[0] . "<BR>If only a single invoice is required, enter the invoice number to print in the Start transaction number to print field and leave the End transaction number to print field blank. Only use the end invoice to print field if you wish to print a sequential range of invoices.";

		$sql = "SELECT TypeNo FROM SysTypes WHERE TypeID=11";

		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);

		echo "<P>The last credit note created was number " . $myrow[0] . "<BR>A sequential range can be printed using the same method as for invoices above. A single credit note can be printed by only entering a start transaction number.";

	} else {

	/*We have a range of invoices to print so get an array of all the company information */
		$CompanyRecord = ReadInCompanyRecord ($db);
		if ($CompanyRecord==0){
			/*CompanyRecord will be 0 if the company information could not be retrieved */
			exit;
		}

		while ($FromTransNo <= $_POST['ToTransNo']){

	/*retrieve the invoice details from the database to print
	notice that salesorder record must be present to print the invoice purging of sales orders will
	nobble the invoice reprints */

			if ($InvOrCredit=="Invoice") {

			   $sql = "SELECT 
			   		DebtorTrans.TranDate,
					DebtorTrans.OvAmount, 
					DebtorTrans.OvDiscount, 
					DebtorTrans.OvFreight, 
					DebtorTrans.OvGST, 
					DebtorTrans.Rate, 
					DebtorTrans.InvText, 
					DebtorTrans.Consignment, 
					DebtorsMaster.Name, 
					DebtorsMaster.Address1, 
					DebtorsMaster.Address2, 
					DebtorsMaster.Address3, 
					DebtorsMaster.Address4, 
					DebtorsMaster.CurrCode, 
					SalesOrders.DeliverTo, 
					SalesOrders.DelAdd1, 
					SalesOrders.DelAdd2, 
					SalesOrders.DelAdd3, 
					SalesOrders.DelAdd4, 
					SalesOrders.CustomerRef, 
					SalesOrders.OrderNo, 
					SalesOrders.OrdDate, 
					Shippers.ShipperName, 
					CustBranch.BrName, 
					CustBranch.BrAddress1, 
					CustBranch.BrAddress2, 
					CustBranch.BrAddress3, 
					CustBranch.BrAddress4,
					Salesman.SalesmanName, 
					DebtorTrans.DebtorNo 
				FROM DebtorTrans, 
					DebtorsMaster, 
					CustBranch, 
					SalesOrders, 
					Shippers, 
					Salesman 
				WHERE DebtorTrans.Order_ = SalesOrders.OrderNo 
				AND DebtorTrans.Type=10 
				AND DebtorTrans.TransNo=" . $FromTransNo . " 
				AND DebtorTrans.ShipVia=Shippers.Shipper_ID 
				AND DebtorTrans.DebtorNo=DebtorsMaster.DebtorNo 
				AND DebtorTrans.DebtorNo=CustBranch.DebtorNo 
				AND DebtorTrans.BranchCode=CustBranch.BranchCode 
				AND CustBranch.Salesman=Salesman.SalesmanCode";
			} else {

			   $sql = "SELECT DebtorTrans.TranDate, 
			   		DebtorTrans.OvAmount, 
					DebtorTrans.OvDiscount, 
					DebtorTrans.OvFreight, 
					DebtorTrans.OvGST, 
					DebtorTrans.Rate, 
					DebtorTrans.InvText, 
					DebtorsMaster.Name, 
					DebtorsMaster.Address1, 
					DebtorsMaster.Address2, 
					DebtorsMaster.Address3, 
					DebtorsMaster.Address4, 
					DebtorsMaster.CurrCode, 
					CustBranch.BrName, 
					CustBranch.BrAddress1, 
					CustBranch.BrAddress2, 
					CustBranch.BrAddress3, 
					CustBranch.BrAddress4, 
					Salesman.SalesmanName, 
					DebtorTrans.DebtorNo 
				FROM DebtorTrans, 
					DebtorsMaster, 
					CustBranch, 
					Salesman 
				WHERE DebtorTrans.Type=11 
				AND DebtorTrans.TransNo=" . $FromTransNo . " 
				AND DebtorTrans.DebtorNo=DebtorsMaster.DebtorNo
				AND DebtorTrans.DebtorNo=CustBranch.DebtorNo 
				AND DebtorTrans.BranchCode=CustBranch.BranchCode 
				AND CustBranch.Salesman=Salesman.SalesmanCode";

			}

			$result=DB_query($sql,$db);
			if (DB_num_rows($result)==0 OR DB_error_no($db)!=0) {
				echo "<P>There was a problem retrieving the invoice (or credit note) details for invoice/credit note number $InvoiceToPrint from the database. To print an <B>invoice</B> the sales order record, the customer transaction record and the branch record for the customer must not have been purged. To print a credit note only requires the customer, transaction, salesman and branch records be available.";
				if ($debug==1){
					echo "The SQL used to get this information (that failed) was:<BR>$sql";
				}
				break;
				include("includes/footer.inc");
				exit;
			} elseif (DB_num_rows($result)==1){

				$myrow = DB_fetch_array($result);
	/* Then there's an invoice (or credit note) to print. So print out the invoice header and GST Number from the company record */
				if (count($SecurityGroups[$_SESSION['AccessLevel']])==1 AND in_array(1, $SecurityGroups[$_SESSION['AccessLevel']]) AND $myrow["DebtorNo"] != $_SESSION["CustomerID"]){
					echo "<P><FONT COLOR=RED SIZE=4>This transaction is addressed to another customer and cannot be displayed for privacy reasons. Please select only transactions relevant to your company.";
					exit;
				}

				$ExchRate = $myrow["Rate"];
				$PageNumber = 1;

				echo "<TABLE WIDTH=100%><TR><TD VALIGN=TOP WIDTH=10%><img src='logo.jpg'></TD><TD BGCOLOR='#BBBBBB'><B>";

				if ($InvOrCredit=="Invoice") {
				   echo "<FONT SIZE=4>TAX INVOICE ";
				} else {
				   echo "<FONT COLOR=RED SIZE=4>TAX CREDIT NOTE ";
				}
				echo "</B>Number " . $FromTransNo . "</FONT><BR><FONT SIZE=1>Tax Authority Ref. " . $CompanyRecord["GSTNo"] . "</TD></TR></TABLE>";

	/*Now print out the logo and company name and address */
				echo "<TABLE WIDTH=100%><TR><TD><FONT SIZE=4 COLOR='#333333'><B>$CompanyName</B></FONT><BR>";
				echo $CompanyRecord["PostalAddress"] . "<BR>";
				echo $CompanyRecord["RegOffice1"] . "<BR>";
				echo $CompanyRecord["RegOffice2"] . "<BR>";
				echo "Telephone: " . $CompanyRecord["Telephone"] . "<BR>";
				echo "Facsimile: " . $CompanyRecord["Fax"] . "<BR>";
				echo "E-mail: " . $CompanyRecord["Email"] . "<BR>";

				echo "</TD><TD WIDTH=50% ALIGN=RIGHT>";

	/*Now the customer charged to details in a sub table within a cell of the main table*/

				echo "<TABLE WIDTH=100%><TR><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Charge To:</B></TD></TR><TR><TD BGCOLOR='#EEEEEE'>";
				echo $myrow["Name"] . "<BR>" . $myrow["Address1"] . "<BR>" . $myrow["Address2"] . "<BR>" . $myrow["Address3"] . "<BR>" . $myrow["Address4"];
				echo "</TD></TR></TABLE>";
				/*end of the small table showing charge to account details */
				echo "Page: " . $PageNumber;
				echo "</TD></TR></TABLE>";
				/*end of the main table showing the company name and charge to details */

				if ($InvOrCredit=="Invoice") {

				   echo "<TABLE WIDTH=100%>
				   			<TR>
				   				<TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Charge Branch:</B></TD>
								<TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Delivered To:</B></TD>
							</TR>";
				   echo "<TR>
				   		<TD BGCOLOR='#EEEEEE'>" .$myrow["BrName"] . "<BR>" . $myrow["BrAddress1"] . "<BR>" . $myrow["BrAddress2"] . "<BR>" . $myrow["BrAddress3"] . "<BR>" . $myrow["BrAddress4"] . "</TD>";

				   	echo "<TD BGCOLOR='#EEEEEE'>" . $myrow["DeliverTo"] . "<BR>" . $myrow["DelAdd1"] . "<BR>" . $myrow["DelAdd2"] . "<BR>" . $myrow["DelAdd3"] . "<BR>" . $myrow["DelAdd4"] . "</TD>";
				   echo "</TR>
				   </TABLE><HR>";
				   
				   echo "<TABLE WIDTH=100%>
				   		<TR>
							<TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Your Order Ref</B></TD>
							<TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Our Order No</B></TD>
							<TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Order Date</B></TD>
							<TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Invoice Date</B></TD>
							<TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Sales Person</FONT></B></TD>
							<TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Shipper</B></TD>
							<TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Consignment Ref</B></TD>
						</TR>";
				   	echo "<TR>
							<TD BGCOLOR='#EEEEEE'>" . $myrow["CustomerRef"] . "</TD>
							<TD BGCOLOR='#EEEEEE'>" .$myrow["OrderNo"] . "</TD>
							<TD BGCOLOR='#EEEEEE'>" . ConvertSQLDate($myrow["OrdDate"]) . "</TD>
							<TD BGCOLOR='#EEEEEE'>" . ConvertSQLDate($myrow["TranDate"]) . "</TD>
							<TD BGCOLOR='#EEEEEE'>" . $myrow["SalesmanName"] . "</TD>
							<TD BGCOLOR='#EEEEEE'>" . $myrow["ShipperName"] . "</TD>
							<TD BGCOLOR='#EEEEEE'>" . $myrow["Consignment"] . "</TD>
						</TR>
					</TABLE>";
					
				   $sql ="SELECT StockMoves.StockID, 
				   		StockMaster.Description, 
						-StockMoves.Qty AS Quantity, 
						StockMoves.DiscountPercent, 
						((1 - StockMoves.DiscountPercent) * StockMoves.Price * " . $ExchRate . "* -StockMoves.Qty) AS FxNet,
						(StockMoves.Price * " . $ExchRate . ") AS FxPrice,
						StockMoves.Narrative, 
						StockMaster.Units 
					FROM StockMoves, 
						StockMaster 
					WHERE StockMoves.StockID = StockMaster.StockID 
					AND StockMoves.Type=10 
					AND StockMoves.TransNo=" . $FromTransNo . " 
					AND StockMoves.Show_On_Inv_Crds=1";

				} else { /* then its a credit note */

				   echo "<TABLE WIDTH=50%><TR>
				   		<TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Branch:</B></TD>
						</TR>";
				   echo "<TR>
				   		<TD BGCOLOR='#EEEEEE'>" .$myrow["BrName"] . "<BR>" . $myrow["BrAddress1"] . "<BR>" . $myrow["BrAddress2"] . "<BR>" . $myrow["BrAddress3"] . "<BR>" . $myrow["BrAddress4"] . "</TD>
					</TR></TABLE>";
				   echo "<HR><TABLE WIDTH=100%><TR>
				   		<TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Date</B></TD>
						<TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Sales Person</FONT></B></TD>
					</TR>";
				   echo "<TR>
				   		<TD BGCOLOR='#EEEEEE'>" . ConvertSQLDate($myrow["TranDate"]) . "</TD>
						<TD BGCOLOR='#EEEEEE'>" . $myrow["SalesmanName"] . "</TD>
					</TR></TABLE>";
				   
				   $sql ="SELECT StockMoves.StockID, 
				   		StockMaster.Description, 
						StockMoves.Qty AS Quantity, 
						StockMoves.DiscountPercent, ((1 - StockMoves.DiscountPercent) * StockMoves.Price * " . $ExchRate . " * StockMoves.Qty) AS FxNet,
						(StockMoves.Price * " . $ExchRate . ") AS FxPrice, 
						StockMaster.Units 
					FROM StockMoves, 
						StockMaster 
					WHERE StockMoves.StockID = StockMaster.StockID 
					AND StockMoves.Type=11 
					AND StockMoves.TransNo=" . $FromTransNo . " 
					AND StockMoves.Show_On_Inv_Crds=1";
				}

				echo "<HR>";
				echo "<CENTER><FONT SIZE=2>All amounts stated in - " . $myrow["CurrCode"] . "</FONT></CENTER>";

				$result=DB_query($sql,$db);
				if (DB_error_no($db)!=0) {
					echo "<BR>There was a problem retrieving the invoice or credit note stock movement details for invoice number " . $FromTransNo . " from the database.";
					if ($debug==1){
						 echo "<BR>The SQL used to get this information (that failed) was:<BR>$sql";
					}
					exit;
				}

				if (DB_num_rows($result)>0){
					echo "<TABLE WIDTH=100% CELLPADDING=5><TR><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Item Code</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Item Description</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Quantity</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Unit</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Price</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Discount</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Net</B></TD></TR>";

					$LineCounter =17;
					$k=0;	//row colour counter

					while ($myrow2=DB_fetch_array($result)){

					      if ($k==1){
						  $RowStarter = "<tr bgcolor='#BBBBBB'>";
						  $k=0;
					      } else {
						  $RowStarter = "<tr bgcolor='#EEEEEE'>";
						  $k=1;
					      }
					      
					      echo $RowStarter;
					      
					      $DisplayPrice = number_format($myrow2["FxPrice"],2);
					      $DisplayQty = number_format($myrow2["Quantity"],2);
					      $DisplayNet = number_format($myrow2["FxNet"],2);

					      if ($myrow2["DiscountPercent"]==0){
						   $DisplayDiscount ="";
					      } else {
						   $DisplayDiscount = number_format($myrow2["DiscountPercent"]*100,2) . "%";
					      }

					      printf ("<TD>%s</TD>
					      		<TD>%s</TD>
							<TD ALIGN=RIGHT>%s</TD>
							<TD ALIGN=RIGHT>%s</TD>
							<TD ALIGN=RIGHT>%s</TD>
							<TD ALIGN=RIGHT>%s</TD>
							<TD ALIGN=RIGHT>%s</TD>
							</TR>",
							$myrow2["StockID"], 
							$myrow2["Description"], 
							$DisplayQty, 
							$myrow2["Units"], 
							$DisplayPrice, 
							$DisplayDiscount, 
							$DisplayNet);

					      if (strlen($myrow2['Narrative'])>1){
					      		echo $RowStarter . "<TD></TD><TD COLSPAN=6>" . $myrow2['Narrative'] . "</TD></TR>";
							$LineCounter++;
					      }
						
					      $LineCounter++;

					      if ($LineCounter == ($PageLength - 2)){

						/* head up a new invoice/credit note page */

						   $PageNumber++;
						   echo "</TABLE><TABLE WIDTH=100%><TR><TD VALIGN=TOP><img src='logo.jpg'></TD><TD BGCOLOR='#BBBBBB'><CENTER><B>";

						   if ($InvOrCredit=="Invoice") {
							    echo "<FONT SIZE=4>TAX INVOICE ";
						   } else {
							    echo "<FONT COLOR=RED SIZE=4>TAX CREDIT NOTE ";
						   }
						   echo "</B>Number " . $FromTransNo . "</FONT><BR><FONT SIZE=1>GST Number - " . $CompanyRecord["GSTNo"] . "</TD></TR><TABLE>";

	/*Now print out company name and address */
						    echo "<TABLE WIDTH=100%><TR><TD><FONT SIZE=4 COLOR='#333333'><B>$CompanyName</B></FONT><BR>";
						    echo $CompanyRecord["PostalAddress"] . "<BR>";
						    echo $CompanyRecord["RegOffice1"] . "<BR>";
						    echo $CompanyRecord["RegOffice2"] . "<BR>";
						    echo "Telephone: " . $CompanyRecord["Telephone"] . "<BR>";
						    echo "Facsimile: " . $CompanyRecord["Fax"] . "<BR>";
						    echo "E-mail: " . $CompanyRecord["Email"] . "<BR>";
						    echo "</TD><TD ALIGN=RIGHT>Page: $PageNumber</TD></TR></TABLE>";
						    echo "<TABLE WIDTH=100% CELLPADDING=5><TR><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Item Code</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Item Description</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Quantity</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Unit</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Price</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Discount</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Net</B></TD></TR>";

						    $LineCounter = 10;

					      } //end if need a new page headed up
					} //end while there are line items to print out
					echo "</TABLE>";
				} /*end if there are stock movements to show on the invoice or credit note*/

				/* check to see enough space left to print the totals/footer */
				$LinesRequiredForText = floor(strlen($myrow["InvText"])/140);

				if ($LineCounter >= ($PageLength - 8 - $LinesRequiredFortext)){

					/* head up a new invoice/credit note page */

					$PageNumber++;
					echo "<TABLE WIDTH=100%><TR><TD VALIGN=TOP><img src='logo.jpg'></TD><TD BGCOLOR='#BBBBBB'><CENTER><B>";

					if ($InvOrCredit=="Invoice") {
					      echo "<FONT SIZE=4>TAX INVOICE ";
					} else {
					      echo "<FONT COLOR=RED SIZE=4>TAX CREDIT NOTE ";
					}
					echo "</B>Number " . $FromTransNo . "</FONT><BR><FONT SIZE=1>GST Number - " . $CompanyRecord["GSTNo"] . "</TD></TR><TABLE>";

	/*Print out the logo and company name and address */
					echo "<TABLE WIDTH=100%><TR><TD><FONT SIZE=4 COLOR='#333333'><B>$CompanyName</B></FONT><BR>";
					echo $CompanyRecord["PostalAddress"] . "<BR>";
					echo $CompanyRecord["RegOffice1"] . "<BR>";
					echo $CompanyRecord["RegOffice2"] . "<BR>";
					echo "Telephone: " . $CompanyRecord["Telephone"] . "<BR>";
					echo "Facsimile: " . $CompanyRecord["Fax"] . "<BR>";
					echo "E-mail: " . $CompanyRecord["Email"] . "<BR>";
					echo "</TD><TD ALIGN=RIGHT>Page: $PageNumber</TD></TR></TABLE>";
					echo "<TABLE WIDTH=100% CELLPADDING=5><TR><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Item Code</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Item Description</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Quantity</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Unit</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Price</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Discount</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>Net</B></TD></TR>";

					$LineCounter = 10;
				}

	/*Space out the footer to the bottom of the page */

				echo "<BR><BR>" . $myrow["InvText"];

				$LineCounter=$LineCounter+2+$LinesRequiredForText;
				while ($LineCounter < ($PageLength -6)){
					echo "<BR>";
					$LineCounter++;
				}

	/*Now print out the footer and totals */

				if ($InvOrCredit=="Invoice") {

				   $DisplaySubTot = number_format($myrow["OvAmount"],2);
				   $DisplayFreight = number_format($myrow["OvFreight"],2);
				   $DisplayTax = number_format($myrow["OvGST"],2);
				   $DisplayTotal = number_format($myrow["OvFreight"]+$myrow["OvGST"]+$myrow["OvAmount"],2);
				} else {
				   $DisplaySubTot = number_format(-$myrow["OvAmount"],2);
				   $DisplayFreight = number_format(-$myrow["OvFreight"],2);
				   $DisplayTax = number_format(-$myrow["OvGST"],2);
				   $DisplayTotal = number_format(-$myrow["OvFreight"]-$myrow["OvGST"]-$myrow["OvAmount"],2);
				}
	/*Print out the invoice text entered */
				echo "<TABLE WIDTH=100%><TR><TD ALIGN=RIGHT>Sub Total</TD><TD ALIGN=RIGHT BGCOLOR='#EEEEEE' WIDTH=15%>$DisplaySubTot</TD></TR>";
				echo "<TR><TD ALIGN=RIGHT>Freight</TD><TD ALIGN=RIGHT BGCOLOR='#EEEEEE'>$DisplayFreight</TD></TR>";
				echo "<TR><TD ALIGN=RIGHT>Tax</TD><TD ALIGN=RIGHT BGCOLOR='#EEEEEE'>$DisplayTax</TD></TR>";
				if ($InvOrCredit=="Invoice"){
				     echo "<TR><TD Align=RIGHT><B>TOTAL INVOICE</B></TD><TD ALIGN=RIGHT BGCOLOR='#EEEEEE'><U><B>$DisplayTotal</B></U></TD></TR>";
				} else {
				     echo "<TR><TD Align=RIGHT><FONT COLOR=RED><B>TOTAL CREDIT</B></FONT></TD><TD ALIGN=RIGHT BGCOLOR='#EEEEEE'><FONT COLOR=RED><U><B>$DisplayTotal</B></U></FONT></TD></TR>";
				}
				echo '</TABLE>';
			} /* end of check to see that there was an invoice record to print */
			$FromTransNo++;
		} /* end loop to print invoices */
	} /*end of if FromTransNo exists */
	include("includes/footer.inc");

} /*end of else not PrintPDF */



function PrintLinesToBottom () {

	global $pdf;
	global $PageNumber;

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

	$pdf->newPage();
	$PageNumber++;

}



?>

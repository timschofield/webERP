<?php

include("includes/SQL_CommonFunctions.inc");
include("includes/DateFunctions.inc");
include("config.php");
include("includes/ConnectDB.inc");

$PageSecurity = 2;

if(!isset($_GET['OrderNo']) AND !isset($_POST['OrderNo'])){
	echo "<CENTER><BR><BR><BR>This page must be called with a purchase order number to print.";
	echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>Back to the Menu</A></CENTER>";
	exit;
}

if (isset($_GET['OrderNo'])){
	$OrderNo = $_GET['OrderNo'];
} elseif (isset($_POST['OrderNo'])){
	$OrderNo = $_POST['OrderNo'];
}

$title="Print Purchase Order Number " . $OrderNo;

$ViewingOnly = 0;
if (isset($_GET['ViewingOnly']) && $_GET['ViewingOnly']!="") {
	$ViewingOnly = $_GET['ViewingOnly'];
} elseif (isset($_POST['ViewingOnly']) && $_POST['ViewingOnly']!="") {
	$ViewingOnly = $_POST['ViewingOnly'];
}


if (($_POST['DoIt']=='OK' AND $_POST['PrintOrEmail']=='Print') OR $ViewingOnly==1){
	$MakePDFThenDisplayIt = True;
} elseif ($_POST['DoIt']=='OK' AND $_POST['PrintOrEmail']=='Email' AND strlen($_POST['EmailTo'])>6){
	$MakePDFThenEmailIt = True;
}


If ($MakePDFThenDisplayIt OR $MakePDFThenEmailIt){

	$PageSize = 'A4_Landscape';

	include("includes/PDFStarter_ros.inc");

	$pdf->addinfo('Title',"Purchase Order");
	$pdf->addinfo('Subject',"Purchase Order Number " . $_GET["OrderNo"]);

	$CompanyRecord = ReadInCompanyRecord ($db);
	if ($CompanyRecord==0){
	/*CompanyRecord will be 0 if the company information could not be retrieved */
	     exit;
	}

	$line_height=16;

	/*retrieve the order details from the database to print */
	$sql = "SELECT PurchOrders.SupplierNo, Suppliers.SuppName, Suppliers.Address1, Suppliers.Address2, Suppliers.Address3, Suppliers.Address4, PurchOrders.Comments, PurchOrders.OrdDate, PurchOrders.Rate, PurchOrders.DatePrinted, PurchOrders.DelAdd1, PurchOrders.DelAdd2, PurchOrders.DelAdd3, PurchOrders.DelAdd4, PurchOrders.AllowPrint, PurchOrders.RequisitionNo, PurchOrders.Initiator, Suppliers.CurrCode FROM PurchOrders, Suppliers WHERE PurchOrders.SupplierNo = Suppliers.SupplierID AND PurchOrders.OrderNo = " . $OrderNo;
	$result=DB_query($sql,$db);
	if (DB_error_no($db)!=0) {
	   echo "There was a problem retrieving the purchase order header details for Order Number " . $OrderNo . " from the database. ";
	   if ($debug==1){
		echo "The SQL used to get this information (that failed) was:<BR>$sql";
	   }
	   break;
	   exit;
	}
	if (DB_num_rows($result)==1){ /*There is ony one order header returned */

	   $POHeader = DB_fetch_array($result);
	   if ($ViewingOnly==0) {
		   if ($POHeader["AllowPrint"]==0){
			  $title = "Purchase Order Already Printed";
			  include("includes/header.inc");
			  echo "<P>Purchase order number " . $OrderNo . " has previously been printed. It was printed on " . ConvertSQLDate($POHeader["DatePrinted"]) . "<BR>To re-print the order, it must be modified to allow a reprint.<BR>This check is there to ensure that duplicate purchase orders are not sent to the supplier resulting in several deliveries of the same supplies.";
			  echo "<BR><A HREF='$rootpath/PO_Header.php?" . SID . "ModifyOrderNumber=" . $OrderNo . "'>Modify the order to allow a reprint</A>";
			  echo "<P><A HREF='$rootpath/PO_SelectPurchOrder.php?" . SID . "'>Select another order</A>";
			  echo "<P><A HREF='$rootpath/index.php?" . SID . "'>Back to the main menu</A>";
			  echo "</body</html>";
			  exit;
		   }
		 }

	   /* Then there's an order to print and its not been printed already (or its been flagged for reprinting)
	   Now ... Has it got any line items */

	   $PageNumber = 1;

	   $sql = "SELECT ItemCode, DeliveryDate, ItemDescription, UnitPrice, Units, QuantityOrd FROM PurchOrderDetails LEFT JOIN StockMaster ON PurchOrderDetails.ItemCode=StockMaster.StockID WHERE OrderNo =" . $OrderNo;
	   $result=DB_query($sql,$db);

	   if (DB_error_no($db)!=0) {
	      echo "<BR>There was a problem retrieving the line details for order number " . $OrderNo . " from the database.";
	      if ($debug==1){
		    echo "<BR>The SQL used to get this information (that failed) was:<BR>$sql";
	      }
	      exit;
	   }

	   if (DB_num_rows($result)>0){
	   /*Yes there are line items to start the ball rolling with a page header */

		include("includes/PO_PDFOrderPageHeader.inc");

		$YPos-=$line_height;

		$OrderTotal = 0;

		while ($POLine=DB_fetch_array($result)){

			$DisplayQty = number_format($POLine["QuantityOrd"],2);
			$DisplayPrice = number_format($POLine["UnitPrice"],2);
			$DisplayDelDate = ConvertSQLDate($POLine["DeliveryDate"],2);
			$DisplayLineTotal = number_format($POLine["UnitPrice"]*$POLine["QuantityOrd"],2);

			$OrderTotal += ($POLine["UnitPrice"]*$POLine["QuantityOrd"]);

			$LeftOvers = $pdf->addTextWrap($Left_Margin+1,$YPos,64,$FontSize,$POLine["ItemCode"], 'left');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+1+64+300,$YPos,85,$FontSize,$DisplayQty, 'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+1+64+300+85+3,$YPos,37,$FontSize,$POLine["Units"], 'left');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+1+64+300+85+3+37,$YPos,60,$FontSize,$DisplayDelDate, 'left');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+1+64+300+85+40+60,$YPos,85,$FontSize,$DisplayPrice, 'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+1+64+300+85+40+60+85,$YPos,85,$FontSize,$DisplayLineTotal, 'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+1+64,$YPos,300,$FontSize,$POLine["ItemDescription"], 'left');
			if (strlen($LeftOvers)>1){
				$LeftOvers = $pdf->addTextWrap($Left_Margin+1+64,$YPos-$line_height,300,$FontSize,$LeftOvers, 'left');
				$YPos-=$line_height;
			}

			if ($YPos-$line_height <= $Bottom_Margin){
		        /* We reached the end of the page so finsih off the page and start a newy */
				$PageNumber++;
				include ("includes/PO_PDFOrderPageHeader.inc");
			} //end if need a new page headed up

			/*increment a line down for the next line item */
			$YPos -= $line_height;

		} //end while there are line items to print out

		if ($YPos-$line_height <= $Bottom_Margin){ // need to ensure space for totals
		        $PageNumber++;
			include ("includes/PO_PDFOrderPageHeader.inc");
		} //end if need a new page headed up


		$DisplayOrderTotal = number_format($OrderTotal,2);
		$YPos = $Bottom_Margin + $line_height;
		$pdf->addText(560,$YPos, 14, "Order Total " . $POHeader["CurrCode"]);
		$LeftOvers = $pdf->addTextWrap($Left_Margin+1+64+300+85+40+60+75,$YPos,95,14,$DisplayOrderTotal, 'right');

	} /*end if there are order details to show on the order*/
    } /* end of check to see that there was an order selected to print */


    if ($MakePDFThenDisplayIt){

    	$buf = $pdf->output();
    	$len = strlen($buf);
    	header("Content-type: application/pdf");
    	header("Content-Length: " . $len);
    	header("Content-Disposition: inline; filename=PurchaseOrder.pdf");
    	header("Expires: 0");
    	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    	header("Pragma: public");

    	$pdf->stream();

    } else { /* must be MakingPDF to email it */

    	include ("includes/header.inc");

    	$pdfcode = $pdf->output();
	$fp = fopen( $reports_dir . "/PurchOrder.pdf","wb");
	fwrite ($fp, $pdfcode);
	fclose ($fp);

	include('includes/htmlMimeMail.php');

	$mail = new htmlMimeMail();
	$attachment = $mail->getFile($reports_dir . "/PurchOrder.pdf");
	$mail->setText('Please find herewith our purchase order number ' . $OrderNo);
	$mail->setSubject('Purchase Order Number ' . $OrderNo);
	$mail->addAttachment($attachment, 'PurchOrder.pdf', 'application/pdf');
	$mail->setFrom("$CompanyName <'" . $CompanyRecord['Email'] ."'>");
	$result = $mail->send(array($_POST['EmailTo']));
	if ($result==1){
		echo "<P>Purchase order $OrderNo has been emailed to " . $_POST['EmailTo'] . " as directed.";

	}

    }

    if ($ViewingOnly==0) {
	$sql = "UPDATE PurchOrders SET AllowPrint=0, DatePrinted='" . Date('Y-m-d') . "' WHERE PurchOrders.OrderNo=" .$OrderNo;
	$result = DB_query($sql,$db);
    }

} /* There was enough info to either print or email the purchase order */
 else { /*the user has just gone into the page need to ask the question whether to print the order or email it to the supplier */
	include ("includes/header.inc");
	echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

	if ($ViewingOnly==1){
		echo "<INPUT TYPE=HIDDEN NAME='ViewingOnly' VALUE=1>";
	}
	echo "<INPUT TYPE=HIDDEN NAME='OrderNo' VALUE=$OrderNo>";
	echo "<CENTER><TABLE><TR><TD>Print or E-mail the Order</TD><TD><SELECT NAME='PrintOrEmail'>";

	if (!isset($_POST['PrintOrEmail'])){
		$_POST['PrintOrEmail'] = 'Print';
	}

	if ($_POST['PrintOrEmail']=='Print'){
		echo "<OPTION SELECTED VALUE='Print'>Print";
		echo "<OPTION VALUE='Email'>Email";
	} else {
		echo "<OPTION VALUE='Print'>Print";
		echo "<OPTION SELECTED VALUE='Email'>Email";
	}
	echo "</SELECT></TD></TR>";

	if ($_POST['PrintOrEmail']=='Email'){
		$SQL = "SELECT Contact, Email FROM SupplierContacts, PurchOrders WHERE SupplierContacts.SupplierID=PurchOrders.SupplierNo AND PurchOrders.OrderNo=$OrderNo";
		$ContactsResult=DB_query($SQL,$db);
		if (DB_error_no($db)!=0) {
			echo "<BR>There was a problem retrieving the contact details for the supplier.";
			if ($debug==1){
				echo "<BR>The SQL used to get this information (that failed) was:<BR>$sql";
			}
			exit;
		}

		if (DB_num_rows($ContactsResult)>0){
			echo "<TR><TD>Email to:</TD><TD><SELECT NAME='EmailTo'>";
			while ($ContactDetails = DB_fetch_array($ContactsResult)){
				if (strlen($ContactDetails['Email'])>2 AND strpos($ContactDetails['Email'],'@')>0){
					if ($_POST['EmailTo']==$ContactDetails['Email']){
						echo "<OPTION SELECTED VALUE='" . $ContactDetails['Email'] . "'>" . $ContactDetails['Contact'] . " - " . $ContactDetails['Email'];
					} else {
						echo "<OPTION VALUE='" . $ContactDetails['Email'] . "'>" . $ContactDetails['Contact'] . " - " . $ContactDetails['Email'];
					}
				}
			}
			echo "</SELECT></TD></TR></TABLE>";
		} else {
			echo "</TABLE><BR>There are no contacts defined for the supplier of this order. First set up supplier contacts";
		}
	} else {
		echo "</TABLE>";
	}
	echo "<BR><INPUT TYPE=SUBMIT NAME='DoIt' VALUE='OK'>";
	echo "</CENTER></FORM></BODY></HTML>";

}


?>

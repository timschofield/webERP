<?php
if (!isset($_GET['TransNo'])  OR $_GET['TransNo']=="") {
	$title="Select Order To Print";
}

$PageSecurity = 2;

include("includes/SQL_CommonFunctions.inc");
include("includes/DateFunctions.inc");

If (isset($_GET['TransNo']) AND $_GET['TransNo']!=""){

	include("config.php");
	include("includes/ConnectDB.inc");

	$PageSize = 'A4_Landscape';

	include("includes/PDFStarter_ros.inc");

/*retrieve the order details from the database to print */

	$sql = "SELECT CustomerRef, Comments, SalesOrders.OrdDate, SalesOrders.DeliverTo, SalesOrders.DelAdd1, SalesOrders.DelAdd2, SalesOrders.DelAdd3, SalesOrders.DelAdd4, DebtorsMaster.Name, DebtorsMaster.Address1, DebtorsMaster.Address2, DebtorsMaster.Address3, DebtorsMaster.Address4, ShipperName, PrintedPackingSlip, DatePackingSlipPrinted, LocationName FROM SalesOrders, DebtorsMaster, Shippers, Locations WHERE SalesOrders.DebtorNo=DebtorsMaster.DebtorNo AND SalesOrders.ShipVia=Shippers.Shipper_ID AND SalesOrders.FromStkLoc=Locations.LocCode AND SalesOrders.OrderNo=" . $_GET['TransNo'];
	$result=DB_query($sql,$db);
	if (DB_error_no($db)!=0) {
	   echo "<P>There was a problem retrieving the order header details for Order Number " . $_GET['TransNo'] . " from the database.";
	   if ($debug==1){
		echo "<BR>The SQL used to get this information (that failed) was:<BR>$sql";
	   }
	   exit;
	}
	if (DB_num_rows($result)==1){ /*There is only one order header returned - thats good! */

	   $myrow = DB_fetch_array($result);
	   if ($myrow["PrintedPackingSlip"]==1 AND ($_GET["Reprint"]!="OK" OR !isset($_GET["Reprint"]))){
	      $title = "Print Packing Slip Error";

	      include("includes/header.inc");
	      echo "<P><FONT SIZE=5 COLOR=RED><B>WARNING:</FONT></B><FONT COLOR=BLACK> The packing slip for order number " . $_GET['TransNo'] . " has previously been printed. It was printed on " . ConvertSQLDate($myrow["DatePackingSlipPrinted"]) . "<BR>This check is there to ensure that duplicate packing slips are not produced and dispatched more than once to the customer.</FONT>";
	      echo "<P><A HREF='$rootpath/PrintCustOrder_generic.php?" . SID . "TransNo=" . $_GET['TransNo'] . "&Reprint=OK'>Do a Re-Print Even Though Previously Printed</A>";
		  include("includes/footer.inc");

	      exit;
	   }
	}
	/* Then there's an order to print and its not been printed already (or its been flagged for reprinting)
	LETS GO */
	$CompanyRecord = ReadInCompanyRecord(&$db);

	$FontSize=12;
	$pdf->selectFont('./fonts/Helvetica.afm');
	$pdf->addinfo('Author',"WEB-ERP " . $Version);
	$pdf->addinfo('Creator',"WEB-ERP http://weberp.sourceforge.net - R&OS PHP-PDF http://www.ros.co.nz");
	$pdf->addinfo('Title',"Customer Laser Packing Slip");
	$pdf->addinfo('Subject',"Laser Packing slip for order " . $_GET['TransNo']);

	for ($i=1;$i<=2;$i++){  /*Print it out twice one copy for customer and one for office */
		if ($i==2){
		$pdf->newPage();
		}

		$line_height=24;

		/* Now ... Has the order got any line items still outstanding to be invoiced */

		$PageNumber = 1;

		$sql = "SELECT StkCode, Description, Quantity, QtyInvoiced, UnitPrice FROM SalesOrderDetails, StockMaster WHERE SalesOrderDetails.StkCode=StockMaster.StockID AND SalesOrderDetails.OrderNo=" . $_GET['TransNo'];
		$result=DB_query($sql,$db);

		if (DB_error_no($db)!=0) {
			echo "<BR>There was a problem retrieving the details for order number ". $_GET['TransNo'] . " from the database.";
			if ($debug==1){
			echo "<BR>The SQL used to get this information (that failed) was:<BR>$sql";
			}
			exit;
		}

		if (DB_num_rows($result)>0){
			/*Yes there are line items to start the ball rolling with a page header */

			include("includes/PDFOrderPageHeader_generic.inc");

			while ($myrow2=DB_fetch_array($result)){

				$DisplayQty = number_format($myrow2["Quantity"],2);
				$DisplayPrevDel = number_format($myrow2["QtyInvoiced"],2);
				$DisplayQtySupplied = number_format($myrow2["Quantity"] - $myrow2["QtyInvoiced"],2);

					$LeftOvers = $pdf->addTextWrap($XPos,$YPos,127,$FontSize,$myrow2["StkCode"]);
					$LeftOvers = $pdf->addTextWrap(147,$YPos,255,$FontSize,$myrow2["Description"]);
					$LeftOvers = $pdf->addTextWrap(400,$YPos,85,$FontSize,$DisplayQty,'right');
					$LeftOvers = $pdf->addTextWrap(503,$YPos,85,$FontSize,$DisplayQtySupplied,'right');
					$LeftOvers = $pdf->addTextWrap(602,$YPos,85,$FontSize,$DisplayPrevDel,'right');

				if ($YPos-$line_height <= 50){
				/* We reached the end of the page so finsih off the page and start a newy */

				$PageNumber++;
				include ("includes/PDFOrderPageHeader_generic.inc");

				} //end if need a new page headed up

				/*increment a line down for the next line item */
				$YPos -= ($line_height);

			} //end while there are line items to print out

		} /*end if there are order details to show on the order*/

		$Copy="Customer";

	} /*end for loop to print the whole lot twice */

	$pdfcode = $pdf->output();
	$len = strlen($pdfcode);

        if ($len<=20){
	      $title = "Print Packing Slip Error";
	      include("includes/header.inc");
	      echo "<p>There were no oustanding items on the order to deliver. A dispatch note cannot be printed";
	      echo "<BR><A HREF='$rootpath/SelectSalesOrder.php?" . SID . "'>Print Another Packing Slip/Order</A>";
	      echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>Back to the menu</A>";
		  include("includes/footer.inc");
	      exit;
        } else {
	      header("Content-type: application/pdf");
	      header("Content-Length: " . $len);
	      header("Content-Disposition: inline; filename=PackingSlip.pdf");
	      header("Expires: 0");
	      header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	      header("Pragma: public");

		  $pdf->Stream();

	      $sql = "UPDATE SalesOrders SET PrintedPackingSlip=1, DatePackingSlipPrinted='" . Date('Y-m-d') . "' WHERE SalesOrders.OrderNo=" .$_GET['TransNo'];
	      $result = DB_query($sql,$db);
        }

} /* end there was an order number that came with the page */

?>

<?php
/* $Revision: 1.4 $ */
$PageSecurity = 2;

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/DateFunctions.inc');

//Get Out if we have no order number to work with
If (!isset($_GET['TransNo']) || $_GET['TransNo']==""){
        $title = _('Select Order To Print');
        include('includes/header.inc');
        echo '<div align=center><br><br><br>';
        prnMsg( _('Select an Order Number to Print before calling this page') , 'error');
        echo '<BR><BR><BR><table class="table_index"><tr><td class="menu_group_item">
                <li><a href="'. $rootpath . '/SelectSalesOrder.php?'. SID .'">' . _('Outstanding Sales Orders') . '</a></li>
                <li><a href="'. $rootpath . '/SelectCompletedOrder.php?'. SID .'">' . _('Completed Sales Orders') . '</a></li>
                </td></tr></table></DIV><BR><BR><BR>';
        include('includes/footer.inc');
        exit();
}

/*retrieve the order details from the database to print */
$ErrMsg = _('There was a problem retrieving the order header details for Order Number') . ' ' . $_GET['TransNo'] . ' ' . _('from the database');

$sql = "SELECT CustomerRef,
			Comments,
			SalesOrders.OrdDate,
			SalesOrders.DeliverTo,
			SalesOrders.DelAdd1,
			SalesOrders.DelAdd2,
			SalesOrders.DelAdd3,
			SalesOrders.DelAdd4,
			DebtorsMaster.Name,
			DebtorsMaster.Address1,
			DebtorsMaster.Address2,
			DebtorsMaster.Address3,
			DebtorsMaster.Address4,
			ShipperName,
			PrintedPackingSlip,
			DatePackingSlipPrinted,
			LocationName
		FROM SalesOrders,
			DebtorsMaster,
			Shippers,
			Locations
		WHERE SalesOrders.DebtorNo=DebtorsMaster.DebtorNo
		AND SalesOrders.ShipVia=Shippers.Shipper_ID
		AND SalesOrders.FromStkLoc=Locations.LocCode
		AND SalesOrders.OrderNo=" . $_GET['TransNo'];

$result=DB_query($sql,$db, $ErrMsg);

//If there are no rows, there's a problem.
if (DB_num_rows($result)==0){
        $title = _('Print Packing Slip Error');
        include('includes/header.inc');
         echo '<div align=center><br><br><br>';
        prnMsg( _('Unable to Locate Order Number') . ' : ' . $_GET['TransNo'] . ' ', 'error');
        echo '<BR><BR><BR><table class="table_index"><tr><td class="menu_group_item">
                <li><a href="'. $rootpath . '/SelectSalesOrder.php?'. SID .'">' . _('Outstanding Sales Orders') . '</a></li>
                <li><a href="'. $rootpath . '/SelectCompletedOrder.php?'. SID .'">' . _('Completed Sales Orders') . '</a></li>
                </td></tr></table></DIV><BR><BR><BR>';
        include('includes/footer.inc');
        exit();
} elseif (DB_num_rows($result)==1){ /*There is only one order header returned - thats good! */

        $myrow = DB_fetch_array($result);
        if ($myrow['PrintedPackingSlip']==1 AND ($_GET['Reprint']!='OK' OR !isset($_GET['Reprint']))){
                $title = _('Print Packing Slip Error');
                include('includes/header.inc');
                echo '<P>';
                prnMsg( _('The packing slip for order number') . ' ' . $_GET['TransNo'] . ' ' .
                        _('has previously been printed') . '. ' . _('It was printed on'). ' ' . ConvertSQLDate($myrow['DatePackingSlipPrinted']) .
                        '<br>' . _('This check is there to ensure that duplicate packing slips are not produced and dispatched more than once to the customer'), 'warn' );
              echo '<P><A HREF="' . $rootpath . '/PrintCustOrder.php?' . SID . 'TransNo=' . $_GET['TransNo'] . '&Reprint=OK">'
                . _('Do a Re-Print') . ' (' . _('On Pre-Printed Stationery') . ') ' . _('Even Though Previously Printed') . '</A><P>' .
                '<A HREF="' . $rootpath. '/PrintCustOrder_generic.php?' . SID . 'TransNo=' . $_GET['TransNo'] . '&Reprint=OK">'. _('Do a Re-Print') . ' (' . _('Plain paper') . ' - ' . _('A4') . ' ' . _('landscape') . ') ' . _('Even Though Previously Printed'). '</A>';

                echo '<BR><BR><BR>';
                echo  _('Or select another Order Number to Print');
                echo '<table class="table_index"><tr><td class="menu_group_item">
                        <li><a href="'. $rootpath . '/SelectSalesOrder.php?'. SID .'">' . _('Outstanding Sales Orders') . '</a></li>
                        <li><a href="'. $rootpath . '/SelectCompletedOrder.php?'. SID .'">' . _('Completed Sales Orders') . '</a></li>
                        </td></tr></table></DIV><BR><BR><BR>';

                include('includes/footer.inc');
                exit;
        }//packing slip has been printed.
}

/*retrieve the order details from the database to print */

/* Then there's an order to print and its not been printed already (or its been flagged for reprinting/ge_Width=807;
)
LETS GO */
$PaperSize = 'A4_Landscape';
include("includes/PDFStarter_ros.inc");

$CompanyRecord = ReadInCompanyRecord(&$db);

$FontSize=12;
$pdf->selectFont('./fonts/Helvetica.afm');
$pdf->addinfo('Title', _('Customer Laser Packing Slip') );
$pdf->addinfo('Subject', _('Laser Packing slip for order') . ' ' . $_GET['TransNo']);

for ($i=1;$i<=2;$i++){  /*Print it out twice one copy for customer and one for office */
	if ($i==2){
		$pdf->newPage();
	}

	$line_height=24;

	/* Now ... Has the order got any line items still outstanding to be invoiced */

	$PageNumber = 1;

	$ErrMsg = _('There was a problem retrieving the order header details for Order Number') . ' ' .
		$_GET['TransNo'] . ' ' . _('from the database');

	$sql = "SELECT StkCode, Description, Quantity, QtyInvoiced, UnitPrice
		FROM SalesOrderDetails INNER JOIN StockMaster
			ON SalesOrderDetails.StkCode=StockMaster.StockID
		WHERE SalesOrderDetails.OrderNo=" . $_GET['TransNo'];
	$result=DB_query($sql,$db, $ErrMsg);

	if (DB_num_rows($result)>0){
		/*Yes there are line items to start the ball rolling with a page header */
		include('includes/PDFOrderPageHeader_generic.inc');

		while ($myrow2=DB_fetch_array($result)){

			$DisplayQty = number_format($myrow2['Quantity'],2);
			$DisplayPrevDel = number_format($myrow2['QtyInvoiced'],2);
			$DisplayQtySupplied = number_format($myrow2['Quantity'] - $myrow2['QtyInvoiced'],2);

			$LeftOvers = $pdf->addTextWrap($XPos,$YPos,127,$FontSize,$myrow2['StkCode']);
			$LeftOvers = $pdf->addTextWrap(147,$YPos,255,$FontSize,$myrow2['Description']);
			$LeftOvers = $pdf->addTextWrap(400,$YPos,85,$FontSize,$DisplayQty,'right');
			$LeftOvers = $pdf->addTextWrap(503,$YPos,85,$FontSize,$DisplayQtySupplied,'right');
			$LeftOvers = $pdf->addTextWrap(602,$YPos,85,$FontSize,$DisplayPrevDel,'right');

			if ($YPos-$line_height <= 50){
			/* We reached the end of the page so finsih off the page and start a newy */

				$PageNumber++;
				include ('includes/PDFOrderPageHeader_generic.inc');

			} //end if need a new page headed up

			/*increment a line down for the next line item */
			$YPos -= ($line_height);

		} //end while there are line items to print out

	} /*end if there are order details to show on the order*/

	$Copy='Customer';

} /*end for loop to print the whole lot twice */

$pdfcode = $pdf->output();
$len = strlen($pdfcode);
if ($len<=20){
        $title = _('Print Packing Slip Error');
        include('includes/header.inc');
        echo '<p>'. _('There were no oustanding items on the order to deliver') . '. ' . _('A dispatch note cannot be printed').
                '<BR><A HREF="' . $rootpath . '/SelectSalesOrder.php?' . SID . '">'. _('Print Another Packing Slip/Order').
                '</A>' . '<BR>'. '<A HREF="' . $rootpath . '/index.php?' . SID . '">' . _('Back to the menu') . '</A>';
        include('includes/footer.inc');
	exit;
} else {
	header('Content-type: application/pdf');
	header('Content-Length: ' . $len);
	header('Content-Disposition: inline; filename=PackingSlip.pdf');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
//echo 'here';
	$pdf->Stream();

	$sql = "UPDATE SalesOrders SET PrintedPackingSlip=1, DatePackingSlipPrinted='" . Date('Y-m-d') . "' WHERE SalesOrders.OrderNo=" .$_GET['TransNo'];
	$result = DB_query($sql,$db);
}

?>

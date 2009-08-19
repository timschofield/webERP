<?php

/* $Revision: 1.1 $ */

$PageSecurity = 2;

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');

//Get Out if we have no order number to work with
If (!isset($_GET['TransNo']) OR $_GET['TransNo']==""){
        $title = _('Select Order To Print');
        include('includes/header.inc');
        echo '<div class=centre><br><br><br>';
        prnMsg( _('Select an Order Number to Print before calling this page') , 'error');
        echo '<br><br><br><table class="table_index"><tr><td class="menu_group_item">
                <li><a href="'. $rootpath . '/SelectSalesOrder.php?'. SID .'">' . _('Outstanding Sales Orders') . '</a></li>
                <li><a href="'. $rootpath . '/SelectCompletedOrder.php?'. SID .'">' . _('Completed Sales Orders') . '</a></li>
                </td></tr></table></div><br><br><br>';
        include('includes/footer.inc');
        exit();
}

/*retrieve the order details from the database to print */
$ErrMsg = _('There was a problem retrieving the order header details for Order Number') . ' ' . $_GET['TransNo'] . ' ' . _('from the database');

$sql = "SELECT salesorders.debtorno,
    		salesorders.customerref,
		salesorders.comments,
		salesorders.orddate,
		salesorders.deliverto,
		salesorders.deladd1,
		salesorders.deladd2,
		salesorders.deladd3,
		salesorders.deladd4,
		salesorders.deladd5,
		salesorders.deladd6,
		salesorders.deliverblind,
		debtorsmaster.name,
		debtorsmaster.address1,
		debtorsmaster.address2,
		debtorsmaster.address3,
		debtorsmaster.address4,
		debtorsmaster.address5,
		debtorsmaster.address6,
		shippers.shippername,
		salesorders.printedpackingslip,
		salesorders.datepackingslipprinted,
		locations.locationname
	FROM salesorders,
		debtorsmaster,
		shippers,
		locations
	WHERE salesorders.debtorno=debtorsmaster.debtorno
	AND salesorders.shipvia=shippers.shipper_id
	AND salesorders.fromstkloc=locations.loccode
	AND salesorders.orderno=" . $_GET['TransNo'];

$result=DB_query($sql,$db, $ErrMsg);

//If there are no rows, there's a problem.
if (DB_num_rows($result)==0){
        $title = _('Print Packing Slip Error');
        include('includes/header.inc');
         echo '<div class=centre><br><br><br>';
        prnMsg( _('Unable to Locate Order Number') . ' : ' . $_GET['TransNo'] . ' ', 'error');
        echo '<br><br><br><table class="table_index"><tr><td class="menu_group_item">
                <li><a href="'. $rootpath . '/SelectSalesOrder.php?'. SID .'">' . _('Outstanding Sales Orders') . '</a></li>
                <li><a href="'. $rootpath . '/SelectCompletedOrder.php?'. SID .'">' . _('Completed Sales Orders') . '</a></li>
                </td></tr></table></div><br><br><br>';
        include('includes/footer.inc');
        exit();
} elseif (DB_num_rows($result)==1){ /*There is only one order header returned - thats good! */

        $myrow = DB_fetch_array($result);
        /* Place the deliver blind variable into a hold variable to used when
        producing the packlist */
        $DeliverBlind = $myrow['deliverblind'];
        if ($myrow['printedpackingslip']==1 AND ($_GET['Reprint']!='OK' OR !isset($_GET['Reprint']))){
                $title = _('Print Packing Slip Error');
                include('includes/header.inc');
                echo '<p>';
                prnMsg( _('The packing slip for order number') . ' ' . $_GET['TransNo'] . ' ' .
                        _('has previously been printed') . '. ' . _('It was printed on'). ' ' . ConvertSQLDate($myrow['datepackingslipprinted']) .
                        '<br>' . _('This check is there to ensure that duplicate packing slips are not produced and dispatched more than once to the customer'), 'warn' );
              echo '<p><a href="' . $rootpath . '/PrintCustOrder.php?' . SID . '&TransNo=' . $_GET['TransNo'] . '&Reprint=OK">'
                . _('Do a Re-Print') . ' (' . _('On Pre-Printed Stationery') . ') ' . _('Even Though Previously Printed') . '</a><p>' .
                '<a href="' . $rootpath. '/PrintCustOrder_generic.php?' . SID . '&TransNo=' . $_GET['TransNo'] . '&Reprint=OK">'. _('Do a Re-Print') . ' (' . _('Plain paper') . ' - ' . _('A4') . ' ' . _('landscape') . ') ' . _('Even Though Previously Printed'). '</a>';

                echo '<br><br><br>';
                echo  _('Or select another Order Number to Print');
                echo '<table class="table_index"><tr><td class="menu_group_item">
                        <li><a href="'. $rootpath . '/SelectSalesOrder.php?'. SID .'">' . _('Outstanding Sales Orders') . '</a></li>
                        <li><a href="'. $rootpath . '/SelectCompletedOrder.php?'. SID .'">' . _('Completed Sales Orders') . '</a></li>
                        </td></tr></table></div><br><br><br>';

                include('includes/footer.inc');
                exit;
        }//packing slip has been printed.
}

/*retrieve the order details from the database to print */

/* Then there's an order to print and its not been printed already (or its been flagged for reprinting/ge_Width=807;
)
LETS GO */
$PaperSize = 'A4_Landscape';
include('includes/PDFStarter.php');

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

	$sql = "SELECT salesorderdetails.stkcode, 
			stockmaster.description, 
			salesorderdetails.quantity, 
			salesorderdetails.qtyinvoiced, 
			salesorderdetails.unitprice,
			salesorderdetails.narrative
		FROM salesorderdetails INNER JOIN stockmaster
			ON salesorderdetails.stkcode=stockmaster.stockid
		WHERE salesorderdetails.orderno=" . $_GET['TransNo'];
	$result=DB_query($sql,$db, $ErrMsg);

	if (DB_num_rows($result)>0){
		/*Yes there are line items to start the ball rolling with a page header */
		include('includes/PDFSalesOrder_generic.inc');

		while ($myrow2=DB_fetch_array($result)){

			$DisplayQty = number_format($myrow2['quantity'],2);
			$DisplayPrevDel = number_format($myrow2['qtyinvoiced'],2);
			$DisplayQtySupplied = number_format($myrow2['quantity'] - $myrow2['qtyinvoiced'],2);
			$itemdesc = $myrow2['description'] . ' - ' . $myrow2['narrative'];
			$LeftOvers = $pdf->addTextWrap($XPos,$YPos,127,$FontSize,$myrow2['stkcode']);
			$LeftOvers = $pdf->addTextWrap(147,$YPos,355,$FontSize,$itemdesc);
			$LeftOvers = $pdf->addTextWrap(400,$YPos,85,$FontSize,$DisplayQty,'right');
			$LeftOvers = $pdf->addTextWrap(503,$YPos,85,$FontSize,$DisplayQtySupplied,'right');
			$LeftOvers = $pdf->addTextWrap(602,$YPos,85,$FontSize,$DisplayPrevDel,'right');

			if ($YPos-$line_height <= 50){
			/* We reached the end of the page so finsih off the page and start a newy */
				$PageNumber++;
				include ('includes/PDFSalesOrder_generic.inc');
			} //end if need a new page headed up
			else{
				/*increment a line down for the next line item */
				$YPos -= ($line_height);
			}
		} //end while there are line items to print out

	} /*end if there are order details to show on the order*/

	$Copy='Customer';

} /*end for loop to print the whole lot twice */

$pdfcode = $pdf->output();
$len = strlen($pdfcode);
if ($len<=20){
        $title = _('Print Packing Slip Error');
        include('includes/header.inc');
        echo '<p>'. _('There were no outstanding items on the order to deliver') . '. ' . _('A packing slip cannot be printed').
                '<br><a href="' . $rootpath . '/SelectSalesOrder.php?' . SID . '">'. _('Print Another Packing Slip/Order').
                '</a>' . '<br>'. '<a href="' . $rootpath . '/index.php?' . SID . '">' . _('Back to the menu') . '</a>';
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

//	$sql = "UPDATE salesorders SET printedpackingslip=1, datepackingslipprinted='" . Date('Y-m-d') . "' WHERE salesorders.orderno=" .$_GET['TransNo'];
//	$result = DB_query($sql,$db);
}

?>

<?php

/* $Revision: 1.12 $ */

$PageSecurity = 2;

include('includes/session.inc');
include('includes/class.pdf.php');
include('includes/SQL_CommonFunctions.inc');

//Get Out if we have no order number to work with
If (!isset($_GET['TransNo']) || $_GET['TransNo']==""){
	$title = _('Select Order To Print');
	include('includes/header.inc');
	echo '<DIV ALIGN=CENTER><BR><BR><BR>';
	prnMsg( _('Select an Order Number to Print before calling this page') , 'error');
	echo '<BR><BR><BR><table class="table_index"><TR><TD CLASS="menu_group_item">
		<LI><A HREF="'. $rootpath . '/SelectSalesOrder.php?'. SID .'">' . _('Outstanding Sales Orders') . '</A></LI>
		<LI><A HREF="'. $rootpath . '/SelectCompletedOrder.php?'. SID .'">' . _('Completed Sales Orders') . '</A></LI>
		</TD></TR></TABLE></DIV><BR><BR><BR>';
	include('includes/footer.inc');
	exit;
}

/*retrieve the order details from the database to print */
$ErrMsg = _('There was a problem retrieving the order header details for Order Number') . ' ' . $_GET['TransNo'] . ' ' . _('from the database');
$sql = "SELECT salesorders.customerref,
		salesorders.comments,
		salesorders.orddate,
		salesorders.deliverto,
		salesorders.deladd1,
		salesorders.deladd2,
		salesorders.deladd3,
		salesorders.deladd4,
		salesorders.deladd5,
		salesorders.deladd6,
		salesorders.debtorno,
		salesorders.branchcode,
                salesorders.deliverydate,
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
	FROM salesorders INNER JOIN debtorsmaster
		ON salesorders.debtorno=debtorsmaster.debtorno
	INNER JOIN shippers
		ON salesorders.shipvia=shippers.shipper_id
	INNER JOIN locations
		ON salesorders.fromstkloc=locations.loccode
	WHERE salesorders.orderno=" . $_GET['TransNo'];

$result=DB_query($sql,$db, $ErrMsg);

//If there are no rows, there's a problem.
if (DB_num_rows($result)==0){
	$title = _('Print Packing Slip Error');
        include('includes/header.inc');
        echo '<div align=center><br><br><br>';
	prnMsg( _('Unable to Locate Order Number') . ' : ' . $_GET['TransNo'] . ' ', 'error');
        echo '<BR><BR><BR><TABLE class="table_index"><TR><TD class="menu_group_item">
                <LI><A HREF="'. $rootpath . '/SelectSalesOrder.php?'. SID .'">' . _('Outstanding Sales Orders') . '</A></LI>
                <LI><A HREF="'. $rootpath . '/SelectCompletedOrder.php?'. SID .'">' . _('Completed Sales Orders') . '</A></LI>
                </TD></TR></TABLE></DIV><BR><BR><BR>';
        include('includes/footer.inc');
        exit();
} elseif (DB_num_rows($result)==1){ /*There is only one order header returned - thats good! */

	$myrow = DB_fetch_array($result);
	if ($myrow['printedpackingslip']==1 AND ($_GET['Reprint']!='OK' OR !isset($_GET['Reprint']))){
		$title = _('Print Packing Slip Error');
	      	include('includes/header.inc');
		echo '<P>';
		prnMsg( _('The packing slip for order number') . ' ' . $_GET['TransNo'] . ' ' .
			_('has previously been printed') . '. ' . _('It was printed on'). ' ' . ConvertSQLDate($myrow['datepackingslipprinted']) .
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
/* Then there's an order to print and it's not been printed already (or its been flagged for reprinting)
LETS GO */


/* Now ... Has the order got any line items still outstanding to be invoiced */

$PageNumber = 1;
$ErrMsg = _('There was a problem retrieving the details for Order Number') . ' ' . $_GET['TransNo'] . ' ' . _('from the database');
$sql = "SELECT salesorderdetails.stkcode,
		stockmaster.description,
		salesorderdetails.quantity,
		salesorderdetails.qtyinvoiced,
		salesorderdetails.unitprice
	FROM salesorderdetails INNER JOIN stockmaster
		ON salesorderdetails.stkcode=stockmaster.stockid
	 WHERE salesorderdetails.orderno=" . $_GET['TransNo'];
$result=DB_query($sql, $db, $ErrMsg);

if (DB_num_rows($result)>0){
/*Yes there are line items to start the ball rolling with a page header */

	/*Set specifically for the stationery being used -needs to be modified for clients own
	packing slip 2 part stationery is recommended so storeman can note differences on and
	a copy retained */

	$Page_Width=807;
	$Page_Height=612;
	$Top_Margin=34;
	$Bottom_Margin=20;
	$Left_Margin=15;
	$Right_Margin=10;


	$PageSize = array(0,0,$Page_Width,$Page_Height);
	$pdf = & new Cpdf($PageSize);
	$FontSize=12;
	$pdf->selectFont('./fonts/Helvetica.afm');
	$pdf->addinfo('Author','webERP ' . $Version);
	$pdf->addinfo('Creator','webERP http://www.weberp.org - R&OS PHP-PDF http://www.ros.co.nz');
	$pdf->addinfo('Title', _('Customer Packing Slip') );
	$pdf->addinfo('Subject', _('Packing slip for order') . ' ' . $_GET['TransNo']);

	$line_height=16;

	include('includes/PDFOrderPageHeader.inc');

	while ($myrow2=DB_fetch_array($result)){

		$DisplayQty = number_format($myrow2['quantity'],2);
		$DisplayPrevDel = number_format($myrow2['qtyinvoiced'],2);
		$DisplayQtySupplied = number_format($myrow2['quantity'] - $myrow2['qtyinvoiced'],2);

		$LeftOvers = $pdf->addTextWrap(13,$YPos,135,$FontSize,$myrow2['stkcode']);
		$LeftOvers = $pdf->addTextWrap(148,$YPos,239,$FontSize,$myrow2['description']);
		$LeftOvers = $pdf->addTextWrap(387,$YPos,90,$FontSize,$DisplayQty,'right');
		$LeftOvers = $pdf->addTextWrap(505,$YPos,90,$FontSize,$DisplayQtySupplied,'right');
		$LeftOvers = $pdf->addTextWrap(604,$YPos,90,$FontSize,$DisplayPrevDel,'right');

		if ($YPos-$line_height <= 136){
	   /* We reached the end of the page so finsih off the page and start a newy */

	      $PageNumber++;
	      include ('includes/PDFOrderPageHeader.inc');

	   } //end if need a new page headed up

	   /*increment a line down for the next line item */
	   $YPos -= ($line_height);

      } //end while there are line items to print out

} /*end if there are order details to show on the order*/

$pdfcode = $pdf->output();
$len = strlen($pdfcode);

if ($len<=20){
	$title = _('Print Packing Slip Error');
	include('includes/header.inc');
	echo '<p>'. _('There were no oustanding items on the order to deliver. A dispatch note cannot be printed').
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

	$pdf->Stream();

	$sql = "UPDATE salesorders SET printedpackingslip=1, datepackingslipprinted='" . Date('Y-m-d') . "' WHERE salesorders.orderno=" .$_GET['TransNo'];
	$result = DB_query($sql,$db);
}

?>
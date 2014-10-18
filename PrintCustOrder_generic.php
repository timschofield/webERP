<?php

/* $Id: PrintCustOrder_generic.php 6447 2013-11-28 15:11:08Z exsonqu $*/


include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');


//Get Out if we have no order number to work with
If (!isset($_GET['TransNo']) OR $_GET['TransNo']==""){
	$Title = _('Select Order To Print');
	include('includes/header.inc');
	echo '<div class="centre"><br /><br /><br />';
	prnMsg( _('Select an Order Number to Print before calling this page') , 'error');
	echo '<br />
			<br />
			<br />
			<table class="table_index">
			<tr>
			<td class="menu_group_item">
            <ul>
			    <li><a href="'. $RootPath . '/SelectSalesOrder.php?">' . _('Outstanding Sales Orders') . '</a></li>
			    <li><a href="'. $RootPath . '/SelectCompletedOrder.php">' . _('Completed Sales Orders') . '</a></li>
            </ul>
			</td>
			</tr>
			</table>
			</div>
			<br />
			<br />
			<br />';
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
			locations.locationname,
			salesorders.fromstkloc
		FROM salesorders INNER JOIN debtorsmaster
		ON salesorders.debtorno=debtorsmaster.debtorno
		INNER JOIN shippers
		ON salesorders.shipvia=shippers.shipper_id
		INNER JOIN locations
		ON salesorders.fromstkloc=locations.loccode
		WHERE salesorders.orderno='" . $_GET['TransNo'] . "'";

if ($_SESSION['SalesmanLogin'] != '') {
	$sql .= " AND salesorders.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
}

$result=DB_query($sql,$db, $ErrMsg);

//If there are no rows, there's a problem.
if (DB_num_rows($result)==0){
	$Title = _('Print Packing Slip Error');
	include('includes/header.inc');
	echo '<div class="centre"><br /><br /><br />';
	prnMsg( _('Unable to Locate Order Number') . ' : ' . $_GET['TransNo'] . ' ', 'error');
	echo '<br />
			<br />
			<br />
			<table class="table_index">
			<tr>
			<td class="menu_group_item">
			<li><a href="'. $RootPath . '/SelectSalesOrder.php">' . _('Outstanding Sales Orders') . '</a></li>
			<li><a href="'. $RootPath . '/SelectCompletedOrder.php">' . _('Completed Sales Orders') . '</a></li>
			</td>
			</tr>
			</table>
			</div>
			<br />
			<br />
			<br />';

	include('includes/footer.inc');
	exit();
} elseif (DB_num_rows($result)==1){ /*There is only one order header returned - thats good! */

        $myrow = DB_fetch_array($result);
        /* Place the deliver blind variable into a hold variable to used when
        producing the packlist */
        $DeliverBlind = $myrow['deliverblind'];
        if ($myrow['printedpackingslip']==1 AND ($_GET['Reprint']!='OK' OR !isset($_GET['Reprint']))){
                $Title = _('Print Packing Slip Error');
                include('includes/header.inc');
                echo '<p>';
                prnMsg( _('The packing slip for order number') . ' ' . $_GET['TransNo'] . ' ' .
                        _('has previously been printed') . '. ' . _('It was printed on'). ' ' . ConvertSQLDate($myrow['datepackingslipprinted']) .
                        '<br />' . _('This check is there to ensure that duplicate packing slips are not produced and dispatched more than once to the customer'), 'warn' );
              echo '<p><a href="' . $RootPath . '/PrintCustOrder.php?TransNo=' . $_GET['TransNo'] . '&Reprint=OK">'
                . _('Do a Re-Print') . ' (' . _('On Pre-Printed Stationery') . ') ' . _('Even Though Previously Printed') . '</a><p>' .
                '<a href="' . $RootPath. '/PrintCustOrder_generic.php?TransNo=' . $_GET['TransNo'] . '&Reprint=OK">' .  _('Do a Re-Print') . ' (' . _('Plain paper') . ' - ' . _('A4') . ' ' . _('landscape') . ') ' . _('Even Though Previously Printed'). '</a>';

                echo '<br /><br /><br />';
                echo  _('Or select another Order Number to Print');
                echo '<table class="table_index">
						<tr>
						<td class="menu_group_item">
                        <li><a href="'. $RootPath . '/SelectSalesOrder.php">' . _('Outstanding Sales Orders') . '</a></li>
                        <li><a href="'. $RootPath . '/SelectCompletedOrder.php">' . _('Completed Sales Orders') . '</a></li>
                        </td>
                        </tr>
                        </table>
                        </div>
                        <br />
                        <br />
                        <br />';

                include('includes/footer.inc');
                exit;
        }//packing slip has been printed.
}

/*retrieve the order details from the database to print */

/* Then there's an order to print and its not been printed already (or its been flagged for reprinting)
LETS GO */

$PaperSize = 'A4_Landscape';
include('includes/PDFStarter.php');
//$pdf->selectFont('./fonts/Helvetica.afm');
$pdf->addInfo('Title', _('Customer Laser Packing Slip') );
$pdf->addInfo('Subject', _('Laser Packing slip for order') . ' ' . $_GET['TransNo']);
$FontSize=12;
$line_height=24;
$PageNumber = 1;
$Copy = 'Office';

$ListCount = 0;

for ($i=1;$i<=2;$i++){  /*Print it out twice one copy for customer and one for office */
	if ($i==2){
		$PageNumber = 1;
		$pdf->newPage();
	}
	/* Now ... Has the order got any line items still outstanding to be invoiced */
	$ErrMsg = _('There was a problem retrieving the order details for Order Number') . ' ' . $_GET['TransNo'] . ' ' . _('from the database');

	$sql = "SELECT salesorderdetails.stkcode,
					stockmaster.description,
					salesorderdetails.quantity,
					salesorderdetails.qtyinvoiced,
					salesorderdetails.unitprice,
					salesorderdetails.narrative,
					stockmaster.mbflag,
					stockmaster.decimalplaces,
					locstock.bin
				FROM salesorderdetails INNER JOIN stockmaster
				ON salesorderdetails.stkcode=stockmaster.stockid
				INNER JOIN locstock
				ON stockmaster.stockid = locstock.stockid
				WHERE locstock.loccode = '" . $myrow['fromstkloc'] . "'
				AND salesorderdetails.orderno='" . $_GET['TransNo'] . "'";
	$result=DB_query($sql,$db, $ErrMsg);

	if (DB_num_rows($result)>0){
		/*Yes there are line items to start the ball rolling with a page header */
		include('includes/PDFOrderPageHeader_generic.inc');

		while ($myrow2=DB_fetch_array($result)){

            $ListCount ++;

			$DisplayQty = locale_number_format($myrow2['quantity'],$myrow2['decimalplaces']);
			$DisplayPrevDel = locale_number_format($myrow2['qtyinvoiced'],$myrow2['decimalplaces']);
			$DisplayQtySupplied = locale_number_format($myrow2['quantity'] - $myrow2['qtyinvoiced'],$myrow2['decimalplaces']);

			$LeftOvers = $pdf->addTextWrap($XPos,$YPos,127,$FontSize,$myrow2['stkcode']);
			$LeftOvers = $pdf->addTextWrap(147,$YPos,255,$FontSize,$myrow2['description']);
			$LeftOvers = $pdf->addTextWrap(400,$YPos,85,$FontSize,$DisplayQty,'right');
			$LeftOvers = $pdf->addTextWrap(487,$YPos,70,$FontSize,$myrow2['bin'],'left');
			$LeftOvers = $pdf->addTextWrap(573,$YPos,85,$FontSize,$DisplayQtySupplied,'right');
			$LeftOvers = $pdf->addTextWrap(672,$YPos,85,$FontSize,$DisplayPrevDel,'right');

			if ($YPos-$line_height <= 50){
			/* We reached the end of the page so finsih off the page and start a newy */
				$PageNumber++;
				include ('includes/PDFOrderPageHeader_generic.inc');
			} //end if need a new page headed up
			else {
				/*increment a line down for the next line item */
				$YPos -= ($line_height);
			}
			if ($myrow2['mbflag']=='A'){
				/*Then its an assembly item - need to explode into it's components for packing list purposes */
				$sql = "SELECT bom.component,
								bom.quantity,
								stockmaster.description,
								stockmaster.decimalplaces
						FROM bom INNER JOIN stockmaster
						ON bom.component=stockmaster.stockid
						WHERE bom.parent='" . $myrow2['stkcode'] . "'";
				$ErrMsg = _('Could not retrieve the components of the ordered assembly item');
				$AssemblyResult = DB_query($sql,$db,$ErrMsg);
				$LeftOvers = $pdf->addTextWrap($XPos,$YPos,150,$FontSize, _('Assembly Components:-'));
				$YPos -= ($line_height);
				/*Loop around all the components of the assembly and list the quantity supplied */
				while ($ComponentRow=DB_fetch_array($AssemblyResult)){
					$DisplayQtySupplied = locale_number_format($ComponentRow['quantity']*($myrow2['quantity'] - $myrow2['qtyinvoiced']),$ComponentRow['decimalplaces']);
					$LeftOvers = $pdf->addTextWrap($XPos,$YPos,127,$FontSize,$ComponentRow['component']);
					$LeftOvers = $pdf->addTextWrap(147,$YPos,255,$FontSize,$ComponentRow['description']);
					$LeftOvers = $pdf->addTextWrap(503,$YPos,85,$FontSize,$DisplayQtySupplied,'right');
					if ($YPos-$line_height <= 50){
						/* We reached the end of the page so finsih off the page and start a newy */
						$PageNumber++;
						include ('includes/PDFOrderPageHeader_generic.inc');
					} //end if need a new page headed up
					 else{
						/*increment a line down for the next line item */
						$YPos -= ($line_height);
					}
				} //loop around all the components of the assembly
			}
		} //end while there are line items to print out

	} /*end if there are order details to show on the order*/

	$Copy='Customer';

} /*end for loop to print the whole lot twice */

if ($ListCount == 0) {
	$Title = _('Print Packing Slip Error');
	include('includes/header.inc');
	echo '<p>' .  _('There were no outstanding items on the order to deliver') . '. ' . _('A packing slip cannot be printed').
			'<br /><a href="' . $RootPath . '/SelectSalesOrder.php">' .  _('Print Another Packing Slip/Order').
			'</a>
			<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
	include('includes/footer.inc');
	exit;
} else {
    	$pdf->OutputD($_SESSION['DatabaseName'] . '_PackingSlip_' . date('Y-m-d') . '.pdf');
    	$pdf->__destruct();
	$sql = "UPDATE salesorders SET printedpackingslip=1,
									datepackingslipprinted='" . Date('Y-m-d') . "'
				WHERE salesorders.orderno='" . $_GET['TransNo'] . "'";
	$result = DB_query($sql,$db);
}

?>

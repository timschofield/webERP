<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.php');

//Get Out if we have no order number to work with
if (!isset($_GET['TransNo']) OR $_GET['TransNo']==''){
	$Title = __('Select Order To Print');
	include('includes/header.php');
	echo '<div class="centre">
		 <br />
		 <br />
		 <br />';
	prnMsg( __('Select an Order Number to Print before calling this page') , 'error');
	echo '<br />
			<br />
			<br />
			<table class="table_index">
			<tr>
			<td class="menu_group_item">
			<ul>
				<li><a href="'. $RootPath . '/SelectSalesOrder.php">' . __('Outstanding Sales Orders') . '</a></li>
				<li><a href="'. $RootPath . '/SelectCompletedOrder.php">' . __('Completed Sales Orders') . '</a></li>
			</ul>
			</td>
			</tr>
			</table>
			</div>
			<br />
			<br />
			<br />';
	include('includes/footer.php');
	exit();
}

/*retrieve the order details from the database to print */
$ErrMsg = __('There was a problem retrieving the order header details for Order Number') . ' ' . $_GET['TransNo'] . ' ' . __('from the database');

$SQL = "SELECT salesorders.debtorno,
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
		FROM salesorders
		INNER JOIN debtorsmaster
			ON salesorders.debtorno=debtorsmaster.debtorno
		INNER JOIN shippers
			ON salesorders.shipvia=shippers.shipper_id
		INNER JOIN locations
			ON salesorders.fromstkloc=locations.loccode
		INNER JOIN locationusers
			ON locationusers.loccode=locations.loccode
			AND locationusers.userid='" .  $_SESSION['UserID'] . "'
			AND locationusers.canview=1
		WHERE salesorders.orderno='" . $_GET['TransNo'] . "'";

if ($_SESSION['SalesmanLogin'] != '') {
	$SQL .= " AND salesorders.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
}

$Result = DB_query($SQL, $ErrMsg);

//If there are no rows, there's a problem.
if (DB_num_rows($Result)==0){
	$Title = __('Print Packing Slip Error');
	include('includes/header.php');
	echo '<div class="centre"><br /><br /><br />';
	prnMsg( __('Unable to Locate Order Number') . ' : ' . $_GET['TransNo'] . ' ', 'error');
	echo '<br />
			<br />
			<br />
			<table class="table_index">
			<tr>
			<td class="menu_group_item">
			<li><a href="'. $RootPath . '/SelectSalesOrder.php">' . __('Outstanding Sales Orders') . '</a></li>
			<li><a href="'. $RootPath . '/SelectCompletedOrder.php">' . __('Completed Sales Orders') . '</a></li>
			</td>
			</tr>
			</table>
			</div>
			<br />
			<br />
			<br />';
	include('includes/footer.php');
	exit();
} elseif (DB_num_rows($Result)==1){ /*There is only one order header returned - thats good! */

		$MyRow = DB_fetch_array($Result);
		/* Place the deliver blind variable into a hold variable to used when
		producing the packlist */
		$DeliverBlind = $MyRow['deliverblind'];
		if ($MyRow['printedpackingslip']==1 AND ($_GET['Reprint']!='OK' OR !isset($_GET['Reprint']))){
				$Title = __('Print Packing Slip');
				$DatePrinted = $MyRow['datepackingslipprinted'];
				include('includes/header.php');
				echo '<p>';
				prnMsg( __('The packing slip for order number') . ' ' . $_GET['TransNo'] . ' ' .
						__('has previously been printed') . '. ' . __('It was printed on'). ' ' . ConvertSQLDate($DatePrinted) .
						'<br />' . __('This check is there to ensure that duplicate packing slips are not produced and dispatched more than once to the customer'), 'warn' );
			  echo '<p><a href="' . $RootPath . '/PrintCustOrder.php?TransNo=' . urlencode($_GET['TransNo']) . '&Reprint=OK">'
				. __('Do a Re-Print') . ' (' . __('On Pre-Printed Stationery') . ') ' . __('Even Though Previously Printed') . '</a><p>' .
				'<a href="' . $RootPath. '/PrintCustOrder_generic.php?TransNo=' . urlencode($_GET['TransNo']) . '&Reprint=OK">' .  __('Do a Re-Print') . ' (' . __('Plain paper') . ' - ' . __('A4') . ' ' . __('landscape') . ') ' . __('Even Though Previously Printed'). '</a>';

				echo '<br /><br /><br />';
				echo  __('Or select another Order Number to Print');
				echo '<table class="table_index">
						<tr>
						<td class="menu_group_item">
						<li><a href="'. $RootPath . '/SelectSalesOrder.php">' . __('Outstanding Sales Orders') . '</a></li>
						<li><a href="'. $RootPath . '/SelectCompletedOrder.php">' . __('Completed Sales Orders') . '</a></li>
						</td>
						</tr>
						</table>
						</div>
						<br />
						<br />
						<br />';

				include('includes/footer.php');
				exit();
		}//packing slip has been printed.
}

/*retrieve the order details from the database to print */

/* Then there's an order to print and it has not been printed already (or its been flagged for reprinting)
LETS GO */

$PaperSize = 'A4_Landscape';
include('includes/PDFStarter.php');

$pdf->addInfo('Title', __('Customer Laser Packing Slip') );
$pdf->addInfo('Subject', __('Laser Packing slip for order') . ' ' . $_GET['TransNo']);
$FontSize=12;
$LineHeight=24;
$PageNumber = 1;
$Copy = 'Office';

$ListCount = 0;

for ($i=1;$i<=2;$i++){  /*Print it out twice one copy for customer and one for office */
	if ($i==2){
		$PageNumber = 1;
		$pdf->newPage();
	}
	/* Now ... Has the order got any line items still outstanding to be invoiced */
	$ErrMsg = __('There was a problem retrieving the order details for Order Number') . ' ' . $_GET['TransNo'] . ' ' . __('from the database');

	$SQL = "SELECT salesorderdetails.stkcode,
					stockmaster.description,
					salesorderdetails.quantity,
					salesorderdetails.qtyinvoiced,
					salesorderdetails.unitprice,
					salesorderdetails.narrative,
					stockmaster.mbflag,
					stockmaster.decimalplaces,
					stockmaster.grossweight,
					stockmaster.volume,
					stockmaster.units,
					stockmaster.controlled,
					stockmaster.serialised,
					pickreqdetails.qtypicked,
					pickreqdetails.detailno,
					custitem.cust_part,
					custitem.cust_description,
					locstock.bin
				FROM salesorderdetails
				INNER JOIN stockmaster
					ON salesorderdetails.stkcode=stockmaster.stockid
				INNER JOIN locstock
					ON stockmaster.stockid = locstock.stockid
				LEFT OUTER JOIN pickreq
					ON pickreq.orderno=salesorderdetails.orderno
					AND pickreq.closed=0
				LEFT OUTER JOIN pickreqdetails
					ON pickreqdetails.prid=pickreq.prid
					AND pickreqdetails.orderlineno=salesorderdetails.orderlineno
				LEFT OUTER JOIN custitem
					ON custitem.debtorno='" . $MyRow['debtorno'] . "'
					AND custitem.stockid=salesorderdetails.stkcode
				WHERE locstock.loccode = '" . $MyRow['fromstkloc'] . "'
					AND salesorderdetails.orderno='" . $_GET['TransNo'] . "'";
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result)>0){
		/*Yes there are line items to start the ball rolling with a page header */
		include('includes/PDFOrderPageHeader_generic.php');

		while ($MyRow2=DB_fetch_array($Result)){

			$ListCount ++;
			$Volume += $MyRow2['quantity'] * $MyRow2['volume'];
			$Weight += $MyRow2['quantity'] * $MyRow2['grossweight'];

			$DisplayQty = locale_number_format($MyRow2['quantity'],$MyRow2['decimalplaces']);
			$DisplayPrevDel = locale_number_format($MyRow2['qtyinvoiced'],$MyRow2['decimalplaces']);

			if ($MyRow2['qtypicked'] > 0) {
				$DisplayQtySupplied = locale_number_format($MyRow2['qtypicked'], $MyRow2['decimalplaces']);
			} else {
				$DisplayQtySupplied = locale_number_format($MyRow2['quantity'] - $MyRow2['qtyinvoiced'],$MyRow2['decimalplaces']);
			}

			$pdf->addTextWrap($XPos,$YPos,127,$FontSize,$MyRow2['stkcode'],'left');
			$pdf->addTextWrap(147,$YPos,255,$FontSize,$MyRow2['description'],'left');
			$pdf->addTextWrap(400,$YPos,85,$FontSize,$DisplayQty,'right');
			$pdf->addTextWrap(487,$YPos,85,$FontSize,$MyRow2['units'],'left');
			$pdf->addTextWrap(527,$YPos,70,$FontSize,$MyRow2['bin'],'left');
			$pdf->addTextWrap(593,$YPos,85,$FontSize,$DisplayQtySupplied,'right');
			$pdf->addTextWrap(692,$YPos,85,$FontSize,$DisplayPrevDel,'right');

			if ($_SESSION['AllowOrderLineItemNarrative'] == 1) {
				// Prints salesorderdetails.narrative:
				$FontSize2 = $FontSize*0.8;// Font size to print salesorderdetails.narrative.
				$Width2 = $Page_Width-$Left_Margin-$Right_Margin-145;// Width to print salesorderdetails.narrative.

				//XPos was 147, same as Description. Move it +10, slight tab in to improve readability
				PrintDetail($pdf, $MyRow2['narrative'], $Bottom_Margin, 157, $YPos, $Width2, $FontSize2, null, 'includes/PDFOrderPageHeader_generic.php');
			}

			if ($YPos-$LineHeight <= 50){
			/* We reached the end of the page so finish off the page and start a newy */
				$PageNumber++;
				include('includes/PDFOrderPageHeader_generic.php');
			} //end if need a new page headed up
			else {
				/*increment a line down for the next line item */
				$YPos -= ($LineHeight);
			}

			if ($MyRow2['cust_part'] > '') {
				$pdf->addTextWrap($XPos, $YPos, 127, $FontSize, $MyRow2['cust_part'], 'right');
				$pdf->addTextWrap(147, $YPos, 255, $FontSize, $MyRow2['cust_description']);
				if ($YPos - $LineHeight <= 50) {
					/* We reached the end of the page so finish off the page and start a newy */
					$PageNumber++;
					include('includes/PDFOrderPageHeader_generic.php');
				} //end if need a new page headed up
				else {
					/*increment a line down for the next line item */
					$YPos -= ($LineHeight);
				}
			}

			if ($MyRow2['mbflag']=='A'){
				/*Then its an assembly item - need to explode into it's components for packing list purposes */
				$SQL = "SELECT bom.component,
								bom.quantity,
								stockmaster.description,
								stockmaster.decimalplaces
						FROM bom
						INNER JOIN stockmaster
							ON bom.component=stockmaster.stockid
						WHERE bom.parent='" . $MyRow2['stkcode'] . "'
							AND bom.effectiveafter <= CURRENT_DATE
							AND bom.effectiveto > CURRENT_DATE";
				$ErrMsg = __('Could not retrieve the components of the ordered assembly item');
				$AssemblyResult = DB_query($SQL, $ErrMsg);
				$pdf->addTextWrap($XPos,$YPos,150,$FontSize, __('Assembly Components:-'));
				$YPos -= ($LineHeight);
				/*Loop around all the components of the assembly and list the quantity supplied */
				while ($ComponentRow=DB_fetch_array($AssemblyResult)){
					$DisplayQtySupplied = locale_number_format($ComponentRow['quantity']*($MyRow2['quantity'] - $MyRow2['qtyinvoiced']),$ComponentRow['decimalplaces']);
					$pdf->addTextWrap($XPos,$YPos,127,$FontSize,$ComponentRow['component']);
					$pdf->addTextWrap(147,$YPos,255,$FontSize,$ComponentRow['description']);
					$pdf->addTextWrap(503,$YPos,85,$FontSize,$DisplayQtySupplied,'right');
					if ($YPos-$LineHeight <= 50){
						/* We reached the end of the page so finsih off the page and start a newy */
						$PageNumber++;
						include('includes/PDFOrderPageHeader_generic.php');
					} //end if need a new page headed up
					 else{
						/*increment a line down for the next line item */
						$YPos -= ($LineHeight);
					}
				} //loop around all the components of the assembly
			}

			if ($MyRow2['controlled'] == '1') {
				$ControlLabel = __('Lot') . ':';
				if ($MyRow2['serialised'] == 1) {
					$ControlLabel = __('Serial') . ':';
				}
				$SerSQL = "SELECT serialno,
									moveqty
							FROM pickserialdetails
							WHERE pickserialdetails.detailno='" . $MyRow2['detailno'] . "'";
				$SerResult = DB_query($SerSQL, $ErrMsg);
				while ($MySer = DB_fetch_array($SerResult)) {
					$pdf->addTextWrap($XPos, $YPos, 127, $FontSize, $ControlLabel, 'right');
					$pdf->addTextWrap(147, $YPos, 255, $FontSize, $MySer['serialno'], 'left');
					$pdf->addTextWrap(147, $YPos, 255, $FontSize, $MySer['moveqty'], 'right');
					if ($YPos - $LineHeight <= 50) {
						/* We reached the end of the page so finsih off the page and start a newy */
						$PageNumber++;
						include('includes/PDFOrderPageHeader_generic.php');
					} //end if need a new page headed up
					else {
						/*increment a line down for the next line item */
						$YPos -= ($LineHeight);
					}
				} //while loop on myser
			} //controlled
		} //end while there are line items to print out

	} /*end if there are order details to show on the order*/

	if ( $Copy != 'Customer' ) {
		$pdf->addTextWrap(375,20,150,$FontSize,'Accepted/Received By:','left');
		$pdf->line(500,20,650,20);
		$pdf->addTextWrap(675,20,50,$FontSize,'Date:','left');
		$pdf->line(710,20,785,20);
	}

	$pdf->addTextWrap(17,20,100,$FontSize,'Volume: ' . round($Volume) . ' GA','left');
	$pdf->addTextWrap(147,20,200,$FontSize,'Weight: ' . round($Weight) . ' LB (approximate)','left');

	$Copy='Customer';
	$Volume = 0;
	$Weight = 0;

} /*end for loop to print the whole lot twice */

if ($ListCount == 0) {
	$Title = __('Print Packing Slip Error');
	include('includes/header.php');
	echo '<p>' .  __('There were no outstanding items on the order to deliver') . '. ' . __('A packing slip cannot be printed').
			'<br /><a href="' . $RootPath . '/SelectSalesOrder.php">' .  __('Print Another Packing Slip/Order').
			'</a>
			<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
	include('includes/footer.php');
	exit();
} else {
	$pdf->OutputD($_SESSION['DatabaseName'] . '_PackingSlip_' . $_GET['TransNo'] . '_' . date('Y-m-d') . '.pdf');
	$pdf->__destruct();
	$SQL = "UPDATE salesorders
			SET printedpackingslip = 1,
				datepackingslipprinted = CURRENT_DATE
			WHERE salesorders.orderno = '" . $_GET['TransNo'] . "'";
	$Result = DB_query($SQL);
}

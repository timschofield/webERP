<?php

require(__DIR__ . '/includes/session.php');

include('includes/class.cpdf.php');
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

$SQL = "SELECT salesorders.customerref,
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
		INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
		WHERE salesorders.orderno='" . $_GET['TransNo'] . "'";

if ($_SESSION['SalesmanLogin'] != '') {
	$SQL .= " AND salesorders.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
}

$Result = DB_query($SQL, $ErrMsg);

//If there are no rows, there's a problem.
if (DB_num_rows($Result)==0){

	$ListCount = 0;

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

/* Javier */	$ListCount = 1;

	$MyRow = DB_fetch_array($Result);
	if ($MyRow['printedpackingslip']==1 AND ($_GET['Reprint']!='OK' OR !isset($_GET['Reprint']))){
		$Title = __('Print Packing Slip Error');
	      	include('includes/header.php');
		echo '<p>';
		prnMsg( __('The packing slip for order number') . ' ' . $_GET['TransNo'] . ' ' .
			__('has previously been printed') . '. ' . __('It was printed on'). ' ' . ConvertSQLDate($MyRow['datepackingslipprinted']) .
			'<br />' . __('This check is there to ensure that duplicate packing slips are not produced and dispatched more than once to the customer'), 'warn' );
	      echo '<p><a href="' . $RootPath . '/PrintCustOrder.php?TransNo=' . $_GET['TransNo'] . '&Reprint=OK">'
		. __('Do a Re-Print') . ' (' . __('On Pre-Printed Stationery') . ') ' . __('Even Though Previously Printed') . '</a><p>' .
		'<a href="' . $RootPath. '/PrintCustOrder_generic.php?TransNo=' . $_GET['TransNo'] . '&Reprint=OK">' .  __('Do a Re-Print') . ' (' . __('Plain paper') . ' - ' . __('A4') . ' ' . __('landscape') . ') ' . __('Even Though Previously Printed'). '</a>';

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
/* Then there's an order to print and it has not been printed already (or its been flagged for reprinting)
LETS GO */


/* Now ... Has the order got any line items still outstanding to be invoiced */

$PageNumber = 1;
$ErrMsg = __('There was a problem retrieving the details for Order Number') . ' ' . $_GET['TransNo'] . ' ' . __('from the database');
$SQL = "SELECT salesorderdetails.stkcode,
			stockmaster.description,
			salesorderdetails.quantity,
			salesorderdetails.qtyinvoiced,
			salesorderdetails.unitprice,
			stockmaster.decimalplaces,
			stockmaster.units
		FROM salesorderdetails INNER JOIN stockmaster
			ON salesorderdetails.stkcode=stockmaster.stockid
		 WHERE salesorderdetails.orderno='" . $_GET['TransNo'] . "'";
$Result = DB_query($SQL, $ErrMsg);

if (DB_num_rows($Result)>0){
/*Yes there are line items to start the ball rolling with a page header */

	/*Set specifically for the stationery being used -needs to be modified for clients own
	packing slip 2 part stationery is recommended so storeman can note differences on and
	a copy retained */

//Javier
//	$Page_Width=807;
	$Page_Width=792;
	$Page_Height=612;
	$Top_Margin=34;
	$Bottom_Margin=20;
	$Left_Margin=15;
	$Right_Margin=10;

// Javier: now I use the native constructor
// Javier: better to not use references
//	$PageSize = array(0,0,$Page_Width,$Page_Height);
	//	$pdf = & new Cpdf($PageSize);
	class Cpdf1 extends Cpdf {

		Public function Footer() {
			// Position at 15 mm from bottom
		$this->SetY(-15);
		//Set font and Page number
		$this->SetFont($UserPdfFont,'I',8);
		$this->Cell(0, 10, __('Page').$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		}
	}

	$pdf = new Cpdf1('L', 'pt', 'LETTER');

	$pdf->addInfo('Creator', 'webERP https://www.weberp.org');
	$pdf->addInfo('Author', 'webERP ' . $Version);
	$pdf->addInfo('Title', __('Customer Packing Slip') );
	$pdf->addInfo('Subject', __('Packing slip for order') . ' ' . $_GET['TransNo']);

/* Javier: I have brought this piece from the pdf class constructor to get it closer to the admin/user,
	I corrected it to match TCPDF, but it still needs check, after which,
	I think it should be moved to each report to provide flexible Document Header and Margins in a per-report basis. */
	$pdf->setAutoPageBreak(0);	// Javier: needs check.
	$pdf->setPrintHeader(false);	// Javier: I added this must be called before Add Page
	$pdf->AddPage();
//	$this->SetLineWidth(1); 	   Javier: It was ok for FPDF but now is too gross with TCPDF. TCPDF defaults to 0'57 pt (0'2 mm) which is ok.
	$pdf->cMargin = 0;		// Javier: needs check.
/* END Brought from class.cpdf.php constructor */
	$pdf->setPrintFooter(true);
	$FontSize=12;
	$LineHeight=16;

	include('includes/PDFOrderPageHeader.php');

	while ($MyRow2=DB_fetch_array($Result)){

		$DisplayQty = locale_number_format($MyRow2['quantity'],$MyRow2['decimalplaces']);
		$DisplayPrevDel = locale_number_format($MyRow2['qtyinvoiced'],$MyRow2['decimalplaces']);
		$DisplayQtySupplied = locale_number_format($MyRow2['quantity'] - $MyRow2['qtyinvoiced'],$MyRow2['decimalplaces']);

		$pdf->addTextWrap(13,$YPos,135,$FontSize,$MyRow2['stkcode'],'left');
		$pdf->addTextWrap(148,$YPos,239,$FontSize,$MyRow2['description'],'left');
		$pdf->addTextWrap(387,$YPos,90,$FontSize,$DisplayQty,'right');
		$pdf->addTextWrap(475,$YPos,20,$FontSize,$MyRow2['units'],'left');
		$pdf->addTextWrap(505,$YPos,90,$FontSize,$DisplayQtySupplied,'right');
		$pdf->addTextWrap(604,$YPos,90,$FontSize,$DisplayPrevDel,'right');

		if ($YPos-$LineHeight <= 136){
	   /* We reached the end of the page so finsih off the page and start a newy */

	      $PageNumber++;
	      include('includes/PDFOrderPageHeader.php');

	   } //end if need a new page headed up

	   /*increment a line down for the next line item */
	   $YPos -= ($LineHeight);

      } //end while there are line items to print out

	$pdf->OutputD($_SESSION['DatabaseName'] . '_Customer_Order_' . $_GET['TransNo'] . '_' . Date('Y-m-d') .'.pdf');
	$pdf-> __destruct();

	$SQL = "UPDATE salesorders
			SET printedpackingslip = 1,
				datepackingslipprinted = CURRENT_DATE
			WHERE salesorders.orderno = '" . $_GET['TransNo'] . "'";
	$Result = DB_query($SQL);
} else {
	$Title = __('Print Packing Slip Error');
	include('includes/header.php');
	echo '<p>' .  __('There were no outstanding items on the order to deliver. A dispatch note cannot be printed').
		'<br /><a href="' . $RootPath . '/SelectSalesOrder.php">' .  __('Print Another Packing Slip/Order').
		'</a>' . '<br />' .  '<a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
	include('includes/footer.php');
	exit();
} /*end if there are order details to show on the order*/

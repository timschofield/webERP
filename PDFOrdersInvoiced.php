<?php


/* $Revision: 1.6 $ */

$PageSecurity = 3;
include ('includes/session.inc');
$title = _('Orders Invoiced Report');

$InputError=0;

if (isset($_POST['FromDate']) AND !Is_Date($_POST['FromDate'])){
	$msg = _('The date from must be specified in the format') . ' ' . $DefaultDateFormat;
	$InputError=1;
	unset($_POST['FromDate']);
}
if (isset($_POST['ToDate']) AND !Is_Date($_POST['ToDate'])){
	$msg = _('The date to must be specified in the format') . ' ' . $DefaultDateFormat;
	$InputError=1;
	unset($_POST['ToDate']);
}
if (Date1GreaterThanDate2($_POST['FromDate'], $_POST['ToDate'])){
	$msg = _('The date to must be after the date from');
	$InputError=1;
	unset($_POST['ToDate']);
	unset($_POST['FromoDate']);
}

if (!isset($_POST['FromDate']) OR !isset($_POST['ToDate']) OR $InputError==1){
     include ('includes/header.inc');
	if ($InputError==1){
		prnMsg($msg,'error');
	}

     echo "<FORM METHOD='post' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>";
     echo '<CENTER><TABLE><TR><TD>' . _('Enter the date from which orders are to be listed') . ":</TD><TD><INPUT TYPE=text NAME='FromDate' MAXLENGTH=10 SIZE=10 VALUE='" . Date($DefaultDateFormat, Mktime(0,0,0,Date('m'),Date('d')-1,Date('y'))) . "'></TD></TR>";
     echo '<TR><TD>' . _('Enter the date to which orders are to be listed') . ":</TD>
     		<TD><INPUT TYPE=text NAME='ToDate' MAXLENGTH=10 SIZE=10 VALUE='" . Date($DefaultDateFormat) . "'></TD></TR>";
     echo '<TR><TD>' . _('Inventory Category') . '</TD><TD>';

     $sql = "SELECT categorydescription, categoryid FROM stockcategory WHERE stocktype<>'D' AND stocktype<>'L'";
     $result = DB_query($sql,$db);


     echo "<SELECT NAME='CategoryID'>";
     echo "<OPTION SELECTED VALUE='All'>" . _('Over All Categories');

     while ($myrow=DB_fetch_array($result)){
	echo '<OPTION VALUE=' . $myrow['categoryid'] . '>' . $myrow['categorydescription'];
     }
     echo '</SELECT></TD></TR>';

     echo '<TR><TD>' . _('Inventory Location') . ":</TD><TD><SELECT NAME='Location'>";
     echo "<OPTION SELECTED VALUE='All'>" . _('All Locations');

     $result= DB_query('SELECT loccode, locationname FROM locations',$db);
     while ($myrow=DB_fetch_array($result)){
	echo "<OPTION VALUE='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
     }
     echo '</SELECT></TD></TR>';

     echo "</TABLE><INPUT TYPE=SUBMIT NAME='Go' VALUE='" . _('Create PDF') . "'></CENTER>";

     include('includes/footer.inc');
     exit;
} else {
	include('includes/PDFStarter.php');
}

if ($_POST['CategoryID']=='All' AND $_POST['Location']=='All'){
	$sql= "SELECT salesorders.orderno,
                      salesorders.debtorno,
                      salesorders.branchcode,
                      salesorders.customerref,
                      salesorders.orddate,
                      salesorders.fromstkloc,
                      salesorders.printedpackingslip,
                      salesorders.datepackingslipprinted,
                      salesorderdetails.stkcode,
                      stockmaster.description,
                      stockmaster.units,
                      stockmaster.decimalplaces,
                      SUM(salesorderdetails.quantity) AS totqty,
                      SUM(salesorderdetails.qtyinvoiced) AS totqtyinvoiced
                  FROM salesorders
                     INNER JOIN salesorderdetails
                     ON salesorders.orderno = salesorderdetails.orderno
                     INNER JOIN stockmaster
                     ON salesorderdetails.stkcode = stockmaster.stockid
                WHERE orddate >='" . FormatDateForSQL($_POST['FromDate']) . "'
                      AND orddate <='" . FormatDateForSQL($_POST['ToDate']) . "'
	      GROUP BY salesorders.orderno,
				salesorders.debtorno,
				salesorders.branchcode,
				salesorders.customerref,
				salesorders.orddate,
				salesorders.fromstkloc,
				salesorderdetails.stkcode,
				stockmaster.description,
				stockmaster.units,
				stockmaster.decimalplaces";


} elseif ($_POST['CategoryID']!='All' AND $_POST['Location']=='All') {
	$sql= "SELECT salesorders.orderno,
                      salesorders.debtorno,
                      salesorders.branchcode,
                      salesorders.customerref,
                      salesorders.orddate,
                      salesorders.fromstkloc,
                      salesorders.printedpackingslip,
                      salesorders.datepackingslipprinted,
                      salesorderdetails.stkcode,
                      stockmaster.description,
                      stockmaster.units,
                      stockmaster.decimalplaces,
                      SUM(salesorderdetails.quantity) AS totqty,
                      SUM(salesorderdetails.qtyinvoiced) AS totqtyinvoiced
                FROM salesorders
                     INNER JOIN salesorderdetails
                     ON salesorders.orderno = salesorderdetails.orderno
                     INNER JOIN stockmaster
                     ON salesorderdetails.stkcode = stockmaster.stockid
                WHERE stockmaster.categoryid ='" . $_POST['CategoryID'] . "'
                      AND orddate >='" . FormatDateForSQL($_POST['FromDate']) . "'
                      AND orddate <='" . FormatDateForSQL($_POST['ToDate']) . "'
	      GROUP BY salesorders.orderno,
				salesorders.debtorno,
				salesorders.branchcode,
				salesorders.customerref,
				salesorders.orddate,
				salesorders.fromstkloc,
				salesorderdetails.stkcode,
				stockmaster.description,
				stockmaster.units,
				stockmaster.decimalplaces";

} elseif ($_POST['CategoryID']=='All' AND $_POST['Location']!='All') {
	$sql= "SELECT salesorders.orderno,
                      salesorders.debtorno,
                      salesorders.branchcode,
                      salesorders.customerref,
                      salesorders.orddate,
                      salesorders.fromstkloc,
                      salesorders.printedpackingslip,
                      salesorders.datepackingslipprinted,
                      salesorderdetails.stkcode,
                      stockmaster.description,
                      stockmaster.units,
                      stockmaster.decimalplaces,
                      SUM(salesorderdetails.quantity) AS totqty,
                      SUM(salesorderdetails.qtyinvoiced) AS totqtyinvoiced
                FROM salesorders
                     INNER JOIN salesorderdetails
                     ON salesorders.orderno = salesorderdetails.orderno
                     INNER JOIN stockmaster
                     ON salesorderdetails.stkcode = stockmaster.stockid
                WHERE salesorders.fromstkloc ='" . $_POST['Location'] . "'
                      AND orddate >='" . FormatDateForSQL($_POST['FromDate']) . "'
                      AND orddate <='" . FormatDateForSQL($_POST['ToDate']) . "'
	      GROUP BY salesorders.orderno,
				salesorders.debtorno,
				salesorders.branchcode,
				salesorders.customerref,
				salesorders.orddate,
				salesorders.fromstkloc,
				salesorderdetails.stkcode,
				stockmaster.description,
				stockmaster.units,
				stockmaster.decimalplaces";

} elseif ($_POST['CategoryID']!='All' AND $_POST['location']!='All'){

	$sql= "SELECT salesorders.orderno,
                      salesorders.debtorno,
                      salesorders.branchcode,
                      salesorders.customerref,
                      salesorders.orddate,
                      salesorders.fromstkloc,
                      salesorderdetails.stkcode,
                      stockmaster.description,
                      stockmaster.units,
                      stockmaster.decimalplaces,
                      SUM(salesorderdetails.quantity) AS totqty,
                      SUM(salesorderdetails.qtyinvoiced) AS totqtyinvoiced
                FROM salesorders
                     INNER JOIN salesorderdetails
                     ON salesorders.orderno = salesorderdetails.orderno
                     INNER JOIN stockmaster
                     ON salesorderdetails.stkcode = stockmaster.stockid
                WHERE STOCKMASTER.CATEGORYID ='" . $_POST['CategoryID'] . "'
                      AND salesorders.fromstkloc ='" . $_POST['Location'] . "'
                      AND orddate >='" . FormatDateForSQL($_POST['FromDate']) . "'
                      AND orddate <='" . FormatDateForSQL($_POST['ToDate']) . "'
		  GROUP BY salesorders.orderno,
				salesorders.debtorno,
				salesorders.branchcode,
				salesorders.customerref,
				salesorders.orddate,
				salesorders.fromstkloc,
				salesorderdetails.stkcode,
				stockmaster.description,
				stockmaster.units,
				stockmaster.decimalplaces";
}

$sql .= ' ORDER BY salesorders.orderno';

$Result=DB_query($sql,$db,'','',false,false); //dont trap errors here

if (DB_error_no($db)!=0){
	include('includes/header.inc');
	prnMsg(_('An error occurred getting the orders details'),'',_('Database Error'));
	if ($debug==1){
		prnMsg( _('The SQL used to get the orders that failed was') . '<BR>' . $sql, '',_('Database Error'));
	}
	include ('includes/footer.inc');
	exit;
} elseif (DB_num_rows($Result)==0){
  	include('includes/header.inc');
	prnMsg(_('There were no orders found in the database within the period from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' '. $_POST['ToDate'] . '. ' . _('Please try again selecting a different date range'),'warn');
	if ($debug==1) {
		prnMsg(_('The SQL that returned no rows was') . '<BR>' . $sql,'',_('Database Error'));
	}
	include('includes/footer.inc');
	exit;
}

/*PDFStarter.php has all the variables for page size and width set up depending on the users default preferences for paper size */

$pdf->addinfo('Title',_('Orders Invoiced Report'));
$pdf->addinfo('Subject',_('Orders from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate']);

$line_height=12;
$PageNumber = 1;

$TotalDiffs = 0;

include ('includes/PDFOrdersInvoicedPageHeader.inc');


$OrderNo =0; /*initialise */
$AccumTotalInv =0;
$AccumOrderTotal =0;

while ($myrow=DB_fetch_array($Result)){
	
	   if($OrderNo != $myrow['orderno']){
	   	if ($AccumOrderTotal !=0){
			$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,120,$FontSize,_('Total Invoiced for order') . ' ' . $OrderNo , 'left');
                 	$LeftOvers = $pdf->addTextWrap($Left_Margin+360,$YPos,80,$FontSize,number_format($AccumOrderTotal,2), 'right');
			$YPos -= ($line_height);
			$AccumOrderTotal =0;
        	}
		$pdf->line($XPos, $YPos,$Page_Width-$Right_Margin, $YPos);
		$YPos -= $line_height;
		if ($YPos - (2 *$line_height) < $Bottom_Margin){
          		/*Then set up a new page */
              		$PageNumber++;
	      		include ('includes/PDFOrdersInvoicedPageHeader.inc');
		} /*end of new page header  */
	   }

	if ($myrow['orderno']!=$OrderNo OR $NewPage){
		  
	        $LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,$myrow['orderno'], 'left');
        	$LeftOvers = $pdf->addTextWrap($Left_Margin+40,$YPos,80,$FontSize,$myrow['debtorno'], 'left');
	        $LeftOvers = $pdf->addTextWrap($Left_Margin+120,$YPos,80,$FontSize,$myrow['branchcode'], 'left');

        	$LeftOvers = $pdf->addTextWrap($Left_Margin+200,$YPos,100,$FontSize,$myrow['customerref'], 'left');
	        $LeftOvers = $pdf->addTextWrap($Left_Margin+300,$YPos,80,$FontSize,ConvertSQLDate($myrow['orddate']), 'left');
        	$LeftOvers = $pdf->addTextWrap($Left_Margin+380,$YPos,20,$FontSize,$myrow['fromstkloc'], 'left');


        	$LeftOvers = $pdf->addTextWrap($Left_Margin+400,$YPos,100,$FontSize,$PackingSlipPrinted, 'left');

	  	$YPos -= ($line_height);

                $OrderNo = $myrow['orderno'];
		 /*Set up the headings for the order */
                $LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,_('Code'), 'center');
                $LeftOvers = $pdf->addTextWrap($Left_Margin+60,$YPos,120,$FontSize,_('Description'), 'center');
                $LeftOvers = $pdf->addTextWrap($Left_Margin+180,$YPos,60,$FontSize,_('Ordered'), 'center');
                $LeftOvers = $pdf->addTextWrap($Left_Margin+240,$YPos,60,$FontSize,_('Invoiced'), 'centre');
                $LeftOvers = $pdf->addTextWrap($Left_Margin+320,$YPos,60,$FontSize,_('Outstanding'), 'center');
	        $YPos -= ($line_height);
		$NewPage = false;
        }
       
       $LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,$myrow['stkcode'], 'left');
       $LeftOvers = $pdf->addTextWrap($Left_Margin+60,$YPos,120,$FontSize,$myrow['description'], 'left');
       $LeftOvers = $pdf->addTextWrap($Left_Margin+180,$YPos,60,$FontSize,number_format($myrow['totqty'],$myrow['decimalplaces']), 'right');
       $LeftOvers = $pdf->addTextWrap($Left_Margin+240,$YPos,60,$FontSize,number_format($myrow['totqtyinvoiced'],$myrow['decimalplaces']), 'right');

       if ($myrow['totqty']>$myrow['totqtyinvoiced']){
             $LeftOvers = $pdf->addTextWrap($Left_Margin+320,$YPos,60,$FontSize,number_format($myrow['totqty']-$myrow['totqtyinvoiced'],$myrow['decimalplaces']), 'right');
       } else {
             $LeftOvers = $pdf->addTextWrap($Left_Margin+320,$YPos,60,$FontSize,_('Complete'), 'left');
       }
	
	$YPos -= ($line_height);
	if ($YPos - (2 *$line_height) < $Bottom_Margin){
        	/*Then set up a new page */
        	$PageNumber++;
		include ('includes/PDFOrdersInvoicedPageHeader.inc');
	} /*end of new page header  */
	
	
       /*OK now get the invoices where the item was charged */
	$sql = 'SELECT debtortrans.order_,
			systypes.typename,
			debtortrans.transno,
	 		stockmoves.price, 
			-stockmoves.qty AS quantity
		FROM debtortrans INNER JOIN stockmoves 
			ON debtortrans.type = stockmoves.type 
			AND debtortrans.transno=stockmoves.transno 
			INNER JOIN systypes ON debtortrans.type=systypes.typeid
		WHERE debtortrans.order_ =' . $OrderNo . "
		AND stockmoves.stockid ='" . $myrow['stkcode'] . "'";
		
	$InvoicesResult =DB_query($sql,$db);
	if (DB_num_rows($InvoicesResult)>0){
		$LeftOvers = $pdf->addTextWrap($Left_Margin+150,$YPos,90,$FontSize,_('Transaction Number'), 'center');
                 $LeftOvers = $pdf->addTextWrap($Left_Margin+240,$YPos,60,$FontSize,_('Quantity'), 'center');
                 $LeftOvers = $pdf->addTextWrap($Left_Margin+300,$YPos,60,$FontSize,_('Price'), 'center');
                 $LeftOvers = $pdf->addTextWrap($Left_Margin+380,$YPos,60,$FontSize,_('Total'), 'centre');
	         $YPos -= ($line_height);
        }
       
	while ($InvRow=DB_fetch_array($InvoicesResult)){
	
		$ValueInvoiced = $InvRow['price']*$InvRow['quantity'];
		
		$LeftOvers = $pdf->addTextWrap($Left_Margin+150,$YPos,90,$FontSize,$InvRow['typename'] . ' ' . $InvRow['transno'], 'left');
                 $LeftOvers = $pdf->addTextWrap($Left_Margin+240,$YPos,60,$FontSize,number_format($InvRow['quantity'],$myrow['decimalplaces']), 'right');
                 $LeftOvers = $pdf->addTextWrap($Left_Margin+300,$YPos,60,$FontSize,number_format($InvRow['price'],2), 'right');
                 $LeftOvers = $pdf->addTextWrap($Left_Margin+360,$YPos,80,$FontSize,number_format($ValueInvoiced,2), 'right');
	         
		 $YPos -= ($line_height);
		 
		 if ($YPos - (2 *$line_height) < $Bottom_Margin){
          		/*Then set up a new page */
              		$PageNumber++;
	      		include ('includes/PDFOrdersInvoicedPageHeader.inc');
		} /*end of new page header  */
		$AccumOrderTotal += $ValueInvoiced;
		$AccumTotalInv += $ValueInvoiced;
	}
	
	
	
      $YPos -= ($line_height);
      if ($YPos - (2 *$line_height) < $Bottom_Margin){
          /*Then set up a new page */
              $PageNumber++;
	      include ('includes/PDFOrdersInvoicedPageHeader.inc');
      } /*end of new page header  */
} /* end of while there are invoiced orders to print */

$YPos -= ($line_height);
$LeftOvers = $pdf->addTextWrap($Left_Margin+260,$YPos,100,$FontSize,_('GRAND TOTAL INVOICED'), 'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin+360,$YPos,80,$FontSize,number_format($AccumTotalInv,2), 'right');
$YPos -= ($line_height);

$pdfcode = $pdf->output();
$len = strlen($pdfcode);
header('Content-type: application/pdf');
header('Content-Length: ' . $len);
header('Content-Disposition: inline; filename=OrdersInvoiced.pdf');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

$pdf->stream();
?>
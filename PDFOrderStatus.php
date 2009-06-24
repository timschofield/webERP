<?php


/* $Revision: 1.10 $ */

$PageSecurity = 3;
include ('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');

$InputError=0;

if (isset($_POST['FromDate']) AND !Is_Date($_POST['FromDate'])){
	$msg = _('The date from must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	$InputError=1;
	unset($_POST['FromDate']);
}
if (isset($_POST['ToDate']) AND !Is_Date($_POST['ToDate'])){
	$msg = _('The date to must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	$InputError=1;
	unset($_POST['ToDate']);
}

if (!isset($_POST['FromDate']) OR !isset($_POST['ToDate'])){

     $title = _('Order Status Report');
     include ('includes/header.inc');

     if ($InputError==1){
     	prnMsg($msg,'error');
     }

     echo "<form method='post' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>";
     echo '<<table><tr><td>' . _('Enter the date from which orders are to be listed') . ":</td><td><input type=text class='date' alt='".$_SESSION['DefaultDateFormat']."' name='FromDate' maxlength=10 size=10 VALUE='" . Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m'),Date('d')-1,Date('y'))) . "'></td></tr>";
     echo '<tr><td>' . _('Enter the date to which orders are to be listed') . ":</td><td>";
     echo "<input type=text class='date' alt='".$_SESSION['DefaultDateFormat']."' name='ToDate' maxlength=10 size=10 VALUE='" . Date($_SESSION['DefaultDateFormat']) . "'></td></tr>";
     echo '<tr><td>' . _('Inventory Category') . '</td><td>';

     $sql = "SELECT categorydescription, categoryid FROM stockcategory WHERE stocktype<>'D' AND stocktype<>'L'";
     $result = DB_query($sql,$db);


     echo "<select name='CategoryID'>";
     echo "<option selected VALUE='All'>" . _('Over All Categories');

     while ($myrow=DB_fetch_array($result)){
		echo '<option VALUE=' . $myrow['categoryid'] . '>' . $myrow['categorydescription'];
     }
     echo '</select></td></tr>';

     echo '<tr><td>' . _('Inventory Location') . ':</td><td><select name="Location">';
     echo '<option selected VALUE="All">' . _('All Locations');

     $result= DB_query('SELECT loccode, locationname FROM locations',$db);
     while ($myrow=DB_fetch_array($result)){
		echo "<option VALUE='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
     }
     echo '</select></td></tr>';

     echo '<tr><td>' . _('Back Order Only') . ":</td><td><select name='BackOrders'>";
     echo "<option selected VALUE='Yes'>" . _('Only Show Back Orders');
     echo "<option VALUE='No'>" . _('Show All Orders');
     echo "</select></td></tr></table><div class='centre'><input type=submit name='Go' VALUE='" . _('Create PDF') . "'></div>";

     include('includes/footer.inc');
     exit;
} else {
	include('includes/ConnectDB.inc');
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
                      salesorderdetails.quantity,
                      salesorderdetails.qtyinvoiced,
                      salesorderdetails.completed
                FROM salesorders
                     INNER JOIN salesorderdetails
                     ON salesorders.orderno = salesorderdetails.orderno
                     INNER JOIN stockmaster
                     ON salesorderdetails.stkcode = stockmaster.stockid
                WHERE salesorders.orddate >='" . FormatDateForSQL($_POST['FromDate']) . "'
                      AND salesorders.orddate <='" . FormatDateForSQL($_POST['ToDate']) . "'
		      AND salesorders.quotation=0";

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
                      salesorderdetails.quantity,
                      salesorderdetails.qtyinvoiced,
                      salesorderdetails.completed
                FROM salesorders
                     INNER JOIN salesorderdetails
                     ON salesorders.orderno = salesorderdetails.orderno
                     INNER JOIN stockmaster
                     ON salesorderdetails.stkcode = stockmaster.stockid
                WHERE stockmaster.categoryid ='" . $_POST['CategoryID'] . "'
                      AND orddate >='" . FormatDateForSQL($_POST['FromDate']) . "'
                      AND orddate <='" . FormatDateForSQL($_POST['ToDate']) . "'
		      AND salesorders.quotation=0";


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
                      salesorderdetails.quantity,
                      salesorderdetails.qtyinvoiced,
                      salesorderdetails.completed
                FROM salesorders
                     INNER JOIN salesorderdetails
                     ON salesorders.orderno = salesorderdetails.orderno
                     INNER JOIN stockmaster
                     ON salesorderdetails.stkcode = stockmaster.stockid
                WHERE salesorders.fromstkloc ='" . $_POST['Location'] . "'
                      AND salesorders.orddate >='" . FormatDateForSQL($_POST['FromDate']) . "'
                      AND salesorders.orddate <='" . FormatDateForSQL($_POST['ToDate']) . "'
		      AND salesorders.quotation=0";


} elseif ($_POST['CategoryID']!='All' AND $_POST['location']!='All'){

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
                      salesorderdetails.quantity,
                      salesorderdetails.qtyinvoiced,
                      salesorderdetails.completed
                FROM salesorders
                     INNER JOIN salesorderdetails
                     ON salesorders.orderno = salesorderdetails.orderno
                     INNER JOIN stockmaster
                     ON salesorderdetails.stkcode = stockmaster.stockid
                WHERE stockmaster.categoryid ='" . $_POST['CategoryID'] . "'
                      AND salesorders.fromstkloc ='" . $_POST['Location'] . "'
                      AND salesorders.orddate >='" . FormatDateForSQL($_POST['FromDate']) . "'
                      AND salesorders.orddate <='" . FormatDateForSQL($_POST['ToDate']) . "'
		      AND salesorders.quotation=0";

}

if ($_POST['BackOrders']=='Yes'){
         $sql .= ' AND salesorderdetails.quantity-salesorderdetails.qtyinvoiced >0';
}

$sql .= ' ORDER BY salesorders.orderno';

$Result=DB_query($sql,$db,'','',false,false); //dont trap errors here

if (DB_error_no($db)!=0){
	include('includes/header.inc');
	echo '<br>' . _('An error occurred getting the orders details');
	if ($debug==1){
		echo '<br>' . _('The SQL used to get the orders that failed was') . '<br>' . $sql;
	}
	include ('includes/footer.inc');
	exit;
} elseif (DB_num_rows($Result)==0){
	$title=_('Order Status Report - No Data');
  	include('includes/header.inc');
	prnMsg(_('There were no orders found in the database within the period from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' '. $_POST['ToDate'] . '. ' . _('Please try again selecting a different date range'),'info');
	include('includes/footer.inc');
	exit;
}


/*PDFStarter.php has all the variables for page size and width set up depending on the users default preferences for paper size */

$pdf->addinfo('Title',_('Order Status Report'));
$pdf->addinfo('Subject',_('Orders from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate']);

$line_height=12;
$PageNumber = 1;

$TotalDiffs = 0;

include ('includes/PDFOrderStatusPageHeader.inc');

$OrderNo =0; /*initialise */

while ($myrow=DB_fetch_array($Result)){


           if ($YPos - (2 *$line_height) < $Bottom_Margin){
       	  /*Then set up a new page */
              $PageNumber++;
	      include ('includes/PDFOrderStatusPageHeader.inc');
	      $OrderNo=0;
           } /*end of new page header  */
	   if($OrderNo!=0 AND $OrderNo != $myrow['orderno']){
		$pdf->line($XPos, $YPos,$Page_Width-$Right_Margin, $YPos);
		$YPos -= $line_height;
	   }

	if ($myrow['orderno']!=$OrderNo	){
	          $LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,$myrow['orderno'], 'left');
        	  $LeftOvers = $pdf->addTextWrap($Left_Margin+40,$YPos,80,$FontSize,$myrow['debtorno'], 'left');
	          $LeftOvers = $pdf->addTextWrap($Left_Margin+120,$YPos,80,$FontSize,$myrow['branchcode'], 'left');

        	  $LeftOvers = $pdf->addTextWrap($Left_Margin+200,$YPos,100,$FontSize,$myrow['customerref'], 'left');
	          $LeftOvers = $pdf->addTextWrap($Left_Margin+300,$YPos,80,$FontSize,ConvertSQLDate($myrow['orddate']), 'left');
        	  $LeftOvers = $pdf->addTextWrap($Left_Margin+380,$YPos,20,$FontSize,$myrow['fromstkloc'], 'left');

	          if ($myrow['printedpackingslip']==1){
        	        $PackingSlipPrinted = _('Printed') . ' ' . ConvertSQLDate($myrow['datepackingslipprinted']);
	          } else {
                	 $PackingSlipPrinted =_('Not yet printed');
          	}

        	  $LeftOvers = $pdf->addTextWrap($Left_Margin+400,$YPos,100,$FontSize,$PackingSlipPrinted, 'left');

	          $YPos -= ($line_height);

                /*Its not the first line */
       	         $OrderNo = $myrow['orderno'];
                 $LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,_('Code'), 'center');
                 $LeftOvers = $pdf->addTextWrap($Left_Margin+60,$YPos,120,$FontSize,_('Description'), 'center');
                 $LeftOvers = $pdf->addTextWrap($Left_Margin+180,$YPos,60,$FontSize,_('Ordered'), 'center');
                 $LeftOvers = $pdf->addTextWrap($Left_Margin+240,$YPos,60,$FontSize,_('Invoiced'), 'centre');
                 $LeftOvers = $pdf->addTextWrap($Left_Margin+320,$YPos,60,$FontSize,_('Outstanding'), 'center');
	         $YPos -= ($line_height);

        }

       $LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,$myrow['stkcode'], 'left');
       $LeftOvers = $pdf->addTextWrap($Left_Margin+60,$YPos,120,$FontSize,$myrow['description'], 'left');
       $LeftOvers = $pdf->addTextWrap($Left_Margin+180,$YPos,60,$FontSize,number_format($myrow['quantity'],$myrow['decimalplaces']), 'right');
       $LeftOvers = $pdf->addTextWrap($Left_Margin+240,$YPos,60,$FontSize,number_format($myrow['qtyinvoiced'],$myrow['decimalplaces']), 'right');

       if ($myrow['quantity']>$myrow['qtyinvoiced']){
             $LeftOvers = $pdf->addTextWrap($Left_Margin+320,$YPos,60,$FontSize,number_format($myrow['quantity']-$myrow['qtyinvoiced'],$myrow['decimalplaces']), 'right');
       } else {
             $LeftOvers = $pdf->addTextWrap($Left_Margin+320,$YPos,60,$FontSize,_('Complete'), 'left');
       }

      $YPos -= ($line_height);
      if ($YPos - (2 *$line_height) < $Bottom_Margin){
          /*Then set up a new page */
              $PageNumber++;
	      include ('includes/PDFOrderStatusPageHeader.inc');
		$OrderNo=0;
      } /*end of new page header  */
} /* end of while there are delivery differences to print */

$pdfcode = $pdf->output();
$len = strlen($pdfcode);
header('Content-type: application/pdf');
header('Content-Length: ' . $len);
header('Content-Disposition: inline; filename=OrderStatus.pdf');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

$pdf->stream();

?>
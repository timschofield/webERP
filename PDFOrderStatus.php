<?php



/* $Revision: 1.1 $ */
$PageSecurity = 3;
include("config.php");
include("includes/SQL_CommonFunctions.inc");
include("includes/DateFunctions.inc");

$InputError=0;

if (!Is_Date($_POST['FromDate'])){
	$msg = _('The date entered was in an unrecognised format it must be specified in the format') . ' ' . $DefaultDateFormat;
	$InputError=1;
}
if (!Is_Date($_POST['ToDate'])){
	$msg = _('The date to must be specified in the format') . ' ' . $DefaultDateFormat;
	$InputError=1;
}

if (!isset($_POST['FromDate']) OR !isset($_POST['ToDate']) OR $InputError==1){
     include ('includes/session.inc');
     $title = _('Order Status Report');
     include ('includes/header.inc');

     echo "<FORM METHOD='post' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>";
     echo "<CENTER><TABLE><TR><TD>" . _("Enter the date from which orders are to be listed:") . "</TD><TD><INPUT TYPE=text NAME='FromDate' MAXLENGTH=10 SIZE=10 VALUE='" . Date($DefaultDateFormat, Mktime(0,0,0,Date("m"),Date('d')-1,Date("y"))) . "'></TD></TR>";
     echo "<TR><TD>" . _("Enter the date to which orders are to be listed:") . "</TD><TD><INPUT TYPE=text NAME='ToDate' MAXLENGTH=10 SIZE=10 VALUE='" . Date($DefaultDateFormat) . "'></TD></TR>";
     echo "<TR><TD>" . _("Inventory Category") . "</TD><TD>";

     $sql = "SELECT CategoryDescription, CategoryID FROM StockCategory WHERE StockType<>'D' AND StockType<>'L'";
     $result = DB_query($sql,$db);


     echo "<SELECT NAME='CategoryID'>";
     echo "<OPTION SELECTED VALUE='All'>" . _('Over All Categories');

     while ($myrow=DB_fetch_array($result)){
	echo "<OPTION VALUE=" . $myrow['CategoryID'] . ">" . $myrow['CategoryDescription'];
     }
     echo "</SELECT></TD></TR>";

     echo "<TR><TD>" . _("Inventory Location:") . "</TD><TD><SELECT NAME='Location'>";
     echo "<OPTION SELECTED VALUE='All'>" . _("All Locations");

     $result= DB_query("SELECT LocCode, LocationName FROM Locations",$db);
     while ($myrow=DB_fetch_array($result)){
	echo "<OPTION VALUE='" . $myrow['LocCode'] . "'>" . $myrow['LocationName'];
     }
     echo "</SELECT></TD></TR>";

     echo "<TR><TD>Back Order Only:</TD><TD><SELECT NAME='BackOrders'>";
     echo "<OPTION SELECTED VALUE='Yes'>" . _('Only Show Back Orders');
     echo "<OPTION VALUE='No'>" . _('Show All Orders');
     echo "</SELECT></TD></TR></TABLE><INPUT TYPE=SUBMIT NAME='Go' VALUE='" . _('Create PDF') . "'></CENTER>";

     if ($InputError==1){
     	echo $msg;
     }
     include("includes/footer.inc");
     exit;
} else {
	include("includes/ConnectDB.inc");
	include("includes/PDFStarter_ros.inc");
}


if ($_POST['CategoryID']=='All' AND $_POST['Location']=='All'){
	$sql= "SELECT SalesOrders.OrderNo,
                      SalesOrders.DebtorNo,
                      SalesOrders.BranchCode,
                      SalesOrders.CustomerRef,
                      SalesOrders.OrdDate,
                      SalesOrders.FromStkLoc,
                      SalesOrders.PrintedPackingSlip,
                      SalesOrders.DatePackingSlipPrinted,
                      SalesOrderDetails.StkCode,
                      StockMaster.Description,
                      StockMaster.Units,
                      StockMaster.DecimalPlaces,
                      SalesOrderDetails.Quantity,
                      SalesOrderDetails.QtyInvoiced,
                      SalesOrderDetails.Completed
                FROM SalesOrders
                     INNER JOIN SalesOrderDetails
                     ON SalesOrders.OrderNo = SalesOrderDetails.OrderNo
                     INNER JOIN StockMaster
                     ON SalesOrderDetails.StkCode = StockMaster.StockID
                WHERE OrdDate >='" . FormatDateForSQL($_POST['FromDate']) . "'
                      AND OrdDate <='" . FormatDateForSQL($_POST['ToDate']) . "'";

} elseif ($_POST['CategoryID']!='All' AND $_POST['Location']=='All') {
	$sql= "SELECT SalesOrders.OrderNo,
                      SalesOrders.DebtorNo,
                      SalesOrders.BranchCode,
                      SalesOrders.CustomerRef,
                      SalesOrders.OrdDate,
                      SalesOrders.FromStkLoc,
                      SalesOrders.PrintedPackingSlip,
                      SalesOrders.DatePackingSlipPrinted,
                      SalesOrderDetails.StkCode,
                      StockMaster.Description,
                      StockMaster.Units,
                      StockMaster.DecimalPlaces,
                      SalesOrderDetails.Quantity,
                      SalesOrderDetails.QtyInvoiced,
                      SalesOrderDetails.Completed
                FROM SalesOrders
                     INNER JOIN SalesOrderDetails
                     ON SalesOrders.OrderNo = SalesOrderDetails.OrderNo
                     INNER JOIN StockMaster
                     ON SalesOrderDetails.StkCode = StockMaster.StockID
                WHERE StockMaster.CategoryID ='" . $_POST['CategoryID'] . "'
                      AND OrdDate >='" . FormatDateForSQL($_POST['FromDate']) . "'
                      AND OrdDate <='" . FormatDateForSQL($_POST['ToDate']) . "'";


} elseif ($_POST['CategoryID']=='All' AND $_POST['Location']!='All') {
	$sql= "SELECT SalesOrders.OrderNo,
                      SalesOrders.DebtorNo,
                      SalesOrders.BranchCode,
                      SalesOrders.CustomerRef,
                      SalesOrders.OrdDate,
                      SalesOrders.FromStkLoc,
                      SalesOrders.PrintedPackingSlip,
                      SalesOrders.DatePackingSlipPrinted,
                      SalesOrderDetails.StkCode,
                      StockMaster.Description,
                      StockMaster.Units,
                      StockMaster.DecimalPlaces,
                      SalesOrderDetails.Quantity,
                      SalesOrderDetails.QtyInvoiced,
                      SalesOrderDetails.Completed
                FROM SalesOrders
                     INNER JOIN SalesOrderDetails
                     ON SalesOrders.OrderNo = SalesOrderDetails.OrderNo
                     INNER JOIN StockMaster
                     ON SalesOrderDetails.StkCode = StockMaster.StockID
                WHERE SalesOrders.FromStkLoc ='" . $_POST['Location'] . "'
                      AND OrdDate >='" . FormatDateForSQL($_POST['FromDate']) . "'
                      AND OrdDate <='" . FormatDateForSQL($_POST['ToDate']) . "'";


} elseif ($_POST['CategoryID']!='All' AND $_POST['location']!='All'){

	$sql= "SELECT SalesOrders.OrderNo,
                      SalesOrders.DebtorNo,
                      SalesOrders.BranchCode,
                      SalesOrders.CustomerRef,
                      SalesOrders.OrdDate,
                      SalesOrders.FromStkLoc,
                      SalesOrders.PrintedPackingSlip,
                      SalesOrders.DatePackingSlipPrinted,
                      SalesOrderDetails.StkCode,
                      StockMaster.Description,
                      StockMaster.Units,
                      StockMaster.DecimalPlaces,
                      SalesOrderDetails.Quantity,
                      SalesOrderDetails.QtyInvoiced,
                      SalesOrderDetails.Completed
                FROM SalesOrders
                     INNER JOIN SalesOrderDetails
                     ON SalesOrders.OrderNo = SalesOrderDetails.OrderNo
                     INNER JOIN StockMaster
                     ON SalesOrderDetails.StkCode = StockMaster.StockID
                WHERE StockMaster.CategoryID ='" . $_POST['CategoryID'] . "'
                      AND SalesOrders.FromStkLoc ='" . $_POST['Location'] . "'
                      AND OrdDate >='" . FormatDateForSQL($_POST['FromDate']) . "'
                      AND OrdDate <='" . FormatDateForSQL($_POST['ToDate']) . "'";

}

if ($_POST['BackOrders']=='Yes'){
         $sql .= ' AND SalesOrderDetails.Quantity-SalesOrderDetails.QtyInvoiced >0';
}

$sql .= ' ORDER BY SalesOrders.OrderNo';

$Result=DB_query($sql,$db,'','',false,false); //dont trap errors here

if (DB_error_no($db)!=0){
	include("includes/header.inc");
	echo "<BR>" . _("An error occurred getting the orders details");
	if ($debug==1){
		echo "<BR>" . _("The SQL used to get the orders (that failed) was:") . "<BR>" . $sql;
	}
	include ("includes/footer.inc");
	exit;
} elseif (DB_num_rows($Result)==0){
  	include("includes/header.inc");
	echo "<BR>" . _('There were no orders found in the database within the period from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' '. $_POST['ToDate'] . ' ' . _('Please try again selecting a different date range');
	if ($debug==1) {
		echo _('The SQL that returned no rows was:') . "<BR>" . $SQL;
	}
	include("includes/footer.inc");
	exit;
}

$CompanyRecord = ReadInCompanyRecord($db);


/*PDFStarter_ros.inc has all the variables for page size and width set up depending on the users default preferences for paper size */

$pdf->addinfo('Title',_('Order Status Report'));
$pdf->addinfo('Subject',_('Orders from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate']);

$line_height=12;
$PageNumber = 1;

$TotalDiffs = 0;

include ("includes/PDFOrderStatusPageHeader.inc");

$OrderNo =0; /*initialise */

while ($myrow=DB_fetch_array($Result)){


           if ($YPos - (2 *$line_height) < $Bottom_Margin){
       	  /*Then set up a new page */
              $PageNumber++;
	      include ("includes/PDFOrderStatusPageHeader.inc");
	      $OrderNo=0;
           } /*end of new page header  */
	   if($OrderNo!=0 AND $OrderNo != $myrow['OrderNo']){
		$pdf->line($XPos, $YPos,$Page_Width-$Right_Margin, $YPos);
		$YPos -= $line_height;
	   }

	if ($myrow['OrderNo']!=$OrderNo	){
	          $LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,$myrow["OrderNo"], 'left');
        	  $LeftOvers = $pdf->addTextWrap($Left_Margin+40,$YPos,80,$FontSize,$myrow["DebtorNo"], 'left');
	          $LeftOvers = $pdf->addTextWrap($Left_Margin+120,$YPos,80,$FontSize,$myrow["BranchCode"], 'left');

        	  $LeftOvers = $pdf->addTextWrap($Left_Margin+200,$YPos,100,$FontSize,$myrow['CustomerRef'], 'left');
	          $LeftOvers = $pdf->addTextWrap($Left_Margin+300,$YPos,80,$FontSize,ConvertSQLDate($myrow["OrdDate"]), 'left');
        	  $LeftOvers = $pdf->addTextWrap($Left_Margin+380,$YPos,20,$FontSize,$myrow["FromStkLoc"], 'left');

	          if ($myrow['PrintedPackingSlip']==1){
        	        $PackingSlipPrinted = _('Printed') . ' ' . ConvertSQLDate($myrow['DatePackingSlipPrinted']);
	          } else {
                	 $PackingSlipPrinted =_('Not yet printed');
          	}

        	  $LeftOvers = $pdf->addTextWrap($Left_Margin+400,$YPos,100,$FontSize,$PackingSlipPrinted, 'left');

	          $YPos -= ($line_height);

                /*Its not the first line */
       	         $OrderNo = $myrow['OrderNo'];
                 $LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,_('Code'), 'center');
                 $LeftOvers = $pdf->addTextWrap($Left_Margin+60,$YPos,120,$FontSize,_('Description'), 'center');
                 $LeftOvers = $pdf->addTextWrap($Left_Margin+180,$YPos,60,$FontSize,_('Ordered'), 'center');
                 $LeftOvers = $pdf->addTextWrap($Left_Margin+240,$YPos,60,$FontSize,_('Invoiced'), 'centre');
                 $LeftOvers = $pdf->addTextWrap($Left_Margin+320,$YPos,60,$FontSize,_('Outstanding'), 'center');
	         $YPos -= ($line_height);

        }

       $LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,$myrow["StkCode"], 'left');
       $LeftOvers = $pdf->addTextWrap($Left_Margin+60,$YPos,120,$FontSize,$myrow["Description"], 'left');
       $LeftOvers = $pdf->addTextWrap($Left_Margin+180,$YPos,60,$FontSize,number_format($myrow["Quantity"],$myrow['DecimalPlaces']), 'right');
       $LeftOvers = $pdf->addTextWrap($Left_Margin+240,$YPos,60,$FontSize,number_format($myrow['QtyInvoiced'],$myrow['DecimalPlaces']), 'right');

       if ($myrow['Quantity']>$myrow['QtyInvoiced']){
             $LeftOvers = $pdf->addTextWrap($Left_Margin+320,$YPos,60,$FontSize,number_format($myrow['Quantity']-$myrow['QtyInvoiced'],$myrow['DecimalPlaces']), 'right');
       } else {
             $LeftOvers = $pdf->addTextWrap($Left_Margin+320,$YPos,60,$FontSize,_('Complete'), 'left');
       }

      $YPos -= ($line_height);
      if ($YPos - (2 *$line_height) < $Bottom_Margin){
          /*Then set up a new page */
              $PageNumber++;
	      include ("includes/PDFOrderStatusPageHeader.inc");
		$OrderNo=0;
      } /*end of new page header  */
} /* end of while there are delivery differences to print */

$pdfcode = $pdf->output();
$len = strlen($pdfcode);
header("Content-type: application/pdf");
header("Content-Length: " . $len);
header("Content-Disposition: inline; filename=OrderStatus.pdf");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Pragma: public");

$pdf->stream();

?>

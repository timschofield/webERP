<?php
/* $Revision: 1.2 $ */
$PageSecurity = 3;
include("includes/SQL_CommonFunctions.inc");
include("includes/DateFunctions.inc");
include("config.php");

$InputError=0;

if (!Is_Date($_POST['FromDate'])){
	$msg = "<BR>The date entered was in an unrecognised format it must be specified in the format $DefaultDateFormat";
	$InputError=1;
}
if (!Is_Date($_POST['ToDate'])){
	$msg = "<BR>The date to must be specified in the format $DefaultDateFormat";
	$InputError=1;
}

if (!isset($_POST['FromDate']) OR !isset($_POST['ToDate']) OR $InputError==1){
     $title = "Delivery Differences Report";
     include ("includes/session.inc");
     include ("includes/header.inc");

     echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . ">";
     echo "<CENTER><TABLE><TR><TD>Enter the date from which variances between orders and deliveries are to be listed:</TD><TD><INPUT TYPE=text NAME='FromDate' MAXLENGTH=10 SIZE=10 VALUE='" . Date($DefaultDateFormat) . "'></TD></TR>";
     echo "<TR><TD>Enter the date to which variances between orders and deliveries are to be listed:</TD><TD><INPUT TYPE=text NAME='ToDate' MAXLENGTH=10 SIZE=10 VALUE='" . Date($DefaultDateFormat) . "'></TD></TR>";
     echo "<TR><TD>Inventory Category</TD><TD>";

     $sql = "SELECT CategoryDescription, CategoryID FROM StockCategory WHERE StockType<>'D' AND StockType<>'L'";
     $result = DB_query($sql,$db);


     echo "<SELECT NAME='CategoryID'>";
     echo "<OPTION SELECTED VALUE='All'>Over All Categories";

     while ($myrow=DB_fetch_array($result)){
	echo "<OPTION VALUE=" . $myrow['CategoryID'] . ">" . $myrow['CategoryDescription'];
     }


     echo "</SELECT></TD></TR>";
     
     echo "<TR><TD>Inventory Location:</TD><TD><SELECT NAME='Location'>";
     echo "<OPTION SELECTED'VALUE='All'>All Locations";

     $result= DB_query("SELECT LocCode, LocationName FROM Locations",$db);
     while ($myrow=DB_fetch_array($result)){
	echo "<OPTION VALUE='" . $myrow['LocCode'] . "'>" . $myrow['LocationName'];
     }
     echo "</SELECT></TD></TR>";

     echo "<TR><TD>Email the report off:</TD><TD><SELECT NAME='Email'>";
     echo "<OPTION SELECTED VALUE='No'>No";
     echo "<OPTION VALUE='Yes'>Yes";
     echo "</SELECT></TD></TR></TABLE><INPUT TYPE=SUBMIT NAME='Go' VALUE='Create PDF'></CENTER>";

     if ($InputError==1){
     	echo $msg;
     }
     exit;
} else {
	include("includes/ConnectDB.inc");
}


if ($_POST['CategoryID']=='All' AND $_POST['Location']=='All'){
	$SQL= "SELECT InvoiceNo, OrderDeliveryDifferencesLog.OrderNo, OrderDeliveryDifferencesLog.StockID, StockMaster.Description, QuantityDiff, TranDate, OrderDeliveryDifferencesLog.DebtorNo, OrderDeliveryDifferencesLog.Branch FROM OrderDeliveryDifferencesLog INNER JOIN StockMaster ON OrderDeliveryDifferencesLog.StockID=StockMaster.StockID INNER JOIN DebtorTrans ON OrderDeliveryDifferencesLog.InvoiceNo=DebtorTrans.TransNo AND DebtorTrans.Type=10 AND TranDate >='" . FormatDateForSQL($_POST['FromDate']) . "' AND TranDate <='" . FormatDateForSQL($_POST['ToDate']) . "'";

} elseif ($_POST['CategoryID']!='All' AND $_POST['Location']=='All') {
	$SQL= "SELECT InvoiceNo, OrderDeliveryDifferencesLog.OrderNo, OrderDeliveryDifferencesLog.StockID, StockMaster.Description, QuantityDiff, TranDate, OrderDeliveryDifferencesLog.DebtorNo, OrderDeliveryDifferencesLog.Branch FROM OrderDeliveryDifferencesLog INNER JOIN StockMaster ON OrderDeliveryDifferencesLog.StockID=StockMaster.StockID INNER JOIN DebtorTrans ON OrderDeliveryDifferencesLog.InvoiceNo=DebtorTrans.TransNo AND DebtorTrans.Type=10 AND TranDate >='" . FormatDateForSQL($_POST['FromDate']) . "' AND TranDate <='" . FormatDateForSQL($_POST['ToDate']) . "' AND CategoryID='" . $_POST['CategoryID'] ."'";

} elseif ($_POST['CategoryID']=='All' AND $_POST['Location']!='All') {
	$SQL = "SELECT InvoiceNo, OrderDeliveryDifferencesLog.OrderNo, OrderDeliveryDifferencesLog.StockID, StockMaster.Description, QuantityDiff, TranDate, OrderDeliveryDifferencesLog.DebtorNo, OrderDeliveryDifferencesLog.Branch FROM OrderDeliveryDifferencesLog INNER JOIN StockMaster ON OrderDeliveryDifferencesLog.StockID=StockMaster.StockID INNER JOIN DebtorTrans ON OrderDeliveryDifferencesLog.InvoiceNo=DebtorTrans.TransNo INNER JOIN SalesOrders ON OrderDeliveryDifferencesLog.OrderNo=SalesOrders.OrderNo WHERE DebtorTrans.Type=10 AND SalesOrders.FromStkLoc='". $_POST['Location'] . "' AND TranDate >='" . FormatDateForSQL($_POST['FromDate']) . "' AND TranDate <='" . FormatDateForSQL($_POST['ToDate']) . "'";

} elseif ($_POST['CategoryID']!='All' AND $_POST['location']!='Áll'){

	$SQL = "SELECT InvoiceNo, OrderDeliveryDifferencesLog.OrderNo, OrderDeliveryDifferencesLog.StockID, StockMaster.Description, QuantityDiff, TranDate, OrderDeliveryDifferencesLog.DebtorNo, OrderDeliveryDifferencesLog.Branch FROM OrderDeliveryDifferencesLog INNER JOIN StockMaster ON OrderDeliveryDifferencesLog.StockID=StockMaster.StockID INNER JOIN DebtorTrans ON OrderDeliveryDifferencesLog.InvoiceNo=DebtorTrans.TransNo AND DebtorTrans.Type=10 INNER JOIN SalesOrders ON OrderDeliveryDifferencesLog.OrderNo = SalesOrders.OrderNo WHERE SalesOrders.FromStkLoc='" . $_POST['Location'] . "' AND CategoryID='" . $_POST['CategoryID'] . "' AND TranDate >='" . FormatDateForSQL($_POST['FromDate']) . "' AND TranDate <= '" . FormatDateForSQL($_POST['ToDate']) . "'"; 


}

$Result=DB_query($SQL,$db);
if (DB_error_no($db)!=0){
   echo "<BR>An error occurred getting the variances between deliveries and orders";
  if ($debug==1){
        echo "<BR>The SQL used to get the variances between deliveries and orders (that failed) was:<BR>$SQL";
  }
  include ("includes/footer.inc");
  exit;
} elseif (DB_num_rows($Result)==0){
  	die ("<BR>There were no variances between deliveries and orders found in the database within the period from " . $_POST['FromDate'] . " to " . $_POST['ToDate'] . ". Please try again selecting a different date range.");
}

$CompanyRecord = ReadInCompanyRecord($db);

include("includes/PDFStarter_ros.inc");

/*PDFStarter_ros.inc has all the variables for page size and width set up depending on the users default preferences for paper size */

$pdf->addinfo('Title',"Variances Between Deliveries and Orders");
$pdf->addinfo('Subject',"Variances Between Deliveries and Orders from  " . $_POST['FromDate'] . " to " . $_POST['ToDate']);

$line_height=12;
$PageNumber = 1;

$TotalDiffs = 0;

include ("includes/PDFDeliveryDifferencesPageHeader.inc");

while ($myrow=DB_fetch_array($Result)){

      $LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,$myrow["InvoiceNo"], 'left');
      $LeftOvers = $pdf->addTextWrap($Left_Margin+40,$YPos,40,$FontSize,$myrow["OrderNo"], 'left');
      $LeftOvers = $pdf->addTextWrap($Left_Margin+80,$YPos,200,$FontSize,$myrow["StockID"] . " - " . $myrow["Description"], 'left');

      $LeftOvers = $pdf->addTextWrap($Left_Margin+280,$YPos,50,$FontSize,number_format($myrow["QuantityDiff"]), 'right');
      $LeftOvers = $pdf->addTextWrap($Left_Margin+335,$YPos,50,$FontSize,$myrow["DebtorNo"], 'left');
      $LeftOvers = $pdf->addTextWrap($Left_Margin+385,$YPos,50,$FontSize,$myrow["Branch"], 'left');
      $LeftOvers = $pdf->addTextWrap($Left_Margin+435,$YPos,50,$FontSize,ConvertSQLDate($myrow["TranDate"]), 'left');

      $YPos -= ($line_height);
      $TotalDiffs++;

      if ($YPos - (2 *$line_height) < $Bottom_Margin){
          /*Then set up a new page */
              $PageNumber++;
	      include ("includes/PDFDeliveryDifferencesPageHeader.inc");
      } /*end of new page header  */
} /* end of while there are delivery differences to print */


$YPos-=$line_height;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,"Total number of differences " . number_format($TotalDiffs), 'left');

if ($_POST['CategoryID']=='All' AND $_POST['Location']=='All'){
	$sql = "SELECT Count(OrderNo) FROM SalesOrderDetails INNER JOIN DebtorTrans ON SalesOrderDetails.OrderNo=DebtorTrans.Order_ WHERE DebtorTrans.TranDate>='" . FormatDateForSQL($_POST['FromDate']) . "' AND TranDate <='" . FormatDateForSQL($_POST['ToDate']) . "'";

} elseif ($_POST['CategoryID']!='All' AND $_POST['Location']=='Áll') {
	$sql = "SELECT Count(OrderNo) FROM SalesOrderDetails INNER JOIN DebtorTrans ON SalesOrderDetails.OrderNo=DebtorTrans.Order_ INNER JOIN StockMaster ON SalesOrderDetails.StkCode=StockMaster.StockID WHERE DebtorTrans.TranDate>='" . FormatDateForSQL($_POST['FromDate']) . "' AND TranDate <='" . FormatDateForSQL($_POST['ToDate']) . "' AND CategoryID='" . $_POST['CategoryID'] . "'";

} elseif ($_POST['CategoryID']=='All' AND $_POST['Location']!='All'){

	$sql = "SELECT Count(SalesOrderDetails.OrderNo) FROM SalesOrderDetails INNER JOIN DebtorTrans ON SalesOrderDetails.OrderNo=DebtorTrans.Order_ INNER JOIN SalesOrders ON SalesOrderDetails.OrderNo = SalesOrders.OrderNo WHERE DebtorTrans.TranDate>='". FormatDateForSQL($_POST['FromDate']) . "' AND TranDate <='" . FormatDateForSQL($_POST['ToDate']) . "' AND FromStkLoc='" . $_POST['Location'] . "'";

} elseif ($_POST['CategoryID'] !='All' AND $_POST['Location'] !='All'){

	$sql = "SELECT Count(OrderNo) FROM SalesorderDetails INNER JOIN DebtorTrans ON SalesOrderDetails.OrderNo=DebtorTrans.Order_ INNER JOIN SalesOrders ON SalesOrderDetails.OrderNo = SalesOrders.OrderNo INNER JOIN StockMaster ON SalesOrderDetails.StkCode = StockMaster.StockID WHERE SalesOrder.FromStkLoc ='" . $_POST['Location'] . "' AND CategoryID='" . $_POST['CategoryID'] . "' AND TranDate >='" . FormatDateForSQL($_POST['FromDate']) . "' AND TranDate <= '" . FormatDateForSQL($_POST['ToDate']) . "'";

}
$result = DB_query($sql,$db);
$myrow=DB_fetch_row($result);
$YPos-=$line_height;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,"Total number of order lines " . number_format($myrow[0]), 'left');

$YPos-=$line_height;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,"DIFOT " . number_format((1-($TotalDiffs/$myrow[0])) * 100,2) . "%", 'left');


$pdfcode = $pdf->output();
$len = strlen($pdfcode);
header("Content-type: application/pdf");
header("Content-Length: " . $len);
header("Content-Disposition: inline; filename=DeliveryDifferences.pdf");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Pragma: public");

$pdf->stream();

if ($_POST['Email']=="Yes"){
	if (file_exists($reports_dir . "/DeliveryDifferences.pdf")){
		unlink($reports_dir . "/DeliveryDifferences.pdf");
	}
    	$fp = fopen( $reports_dir . "/DeliveryDifferences.pdf","wb");
	fwrite ($fp, $pdfcode);
	fclose ($fp);

	include('includes/htmlMimeMail.php');

	$mail = new htmlMimeMail();
	$attachment = $mail->getFile($reports_dir . "/DeliveryDifferences.pdf");
	$mail->setText("Please find herewith delivery differences report from " . $_POST['FromDate'] . " to " . $_POST['ToDate']);
	$mail->addAttachment($attachment, 'DeliveryDifferences.pdf', 'application/pdf');
	$mail->setFrom(array('$CompanyName <' . $CompanyRecord["Email"] .'>'));

	/* $DelDiffsRecipients defined in config.php */
	$result = $mail->send($DelDiffsRecipients);
}

?>

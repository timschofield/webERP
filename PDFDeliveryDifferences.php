<?php
/* $Revision: 1.5 $ */
include("config.php");
$PageSecurity = 3;
include("includes/SQL_CommonFunctions.inc");
include("includes/DateFunctions.inc");

$InputError=0;

if (!Is_Date($_POST['FromDate'])){
	$msg = '<BR>' . _('The date entered was in an unrecognised format it must be specified in the format') . ' ' . $DefaultDateFormat;
	$InputError=1;
}
if (!Is_Date($_POST['ToDate'])){
	$msg = '<BR>' . _('The date to must be specified in the format') .  $DefaultDateFormat;
	$InputError=1;
}

if (!isset($_POST['FromDate']) OR !isset($_POST['ToDate']) OR $InputError==1){
     include ("includes/session.inc");
     $title = _('Delivery Differences Report');
     include ("includes/header.inc");

     echo "<FORM METHOD='post' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>";
     echo "<CENTER><TABLE><TR><TD>" . _("Enter the date from which variances between orders and deliveries are to be listed:") . "</TD><TD><INPUT TYPE=text NAME='FromDate' MAXLENGTH=10 SIZE=10 VALUE='" . Date($DefaultDateFormat, Mktime(0,0,0,Date("m")-1,0,Date("y"))) . "'></TD></TR>";
     echo "<TR><TD>" . _("Enter the date to which variances between orders and deliveries are to be listed:") . "</TD><TD><INPUT TYPE=text NAME='ToDate' MAXLENGTH=10 SIZE=10 VALUE='" . Date($DefaultDateFormat) . "'></TD></TR>";
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

     echo "<TR><TD>Email the report off:</TD><TD><SELECT NAME='Email'>";
     echo "<OPTION SELECTED VALUE='No'>" . _('No');
     echo "<OPTION VALUE='Yes'>" . _('Yes');
     echo "</SELECT></TD></TR></TABLE><INPUT TYPE=SUBMIT NAME='Go' VALUE='" . _('Create PDF') . "'></CENTER>";

     if ($InputError==1){
     	echo $msg;
     }
     include("includes/footer.inc");
     exit;
} else {
	include("includes/ConnectDB.inc");
}

if ($_POST['CategoryID']=='All' AND $_POST['Location']=='All'){
	$sql= "SELECT InvoiceNo, OrderDeliveryDifferencesLog.OrderNo, OrderDeliveryDifferencesLog.StockID, StockMaster.Description, QuantityDiff, TranDate, OrderDeliveryDifferencesLog.DebtorNo, OrderDeliveryDifferencesLog.Branch FROM OrderDeliveryDifferencesLog INNER JOIN StockMaster ON OrderDeliveryDifferencesLog.StockID=StockMaster.StockID INNER JOIN DebtorTrans ON OrderDeliveryDifferencesLog.InvoiceNo=DebtorTrans.TransNo AND DebtorTrans.Type=10 AND TranDate >='" . FormatDateForSQL($_POST['FromDate']) . "' AND TranDate <='" . FormatDateForSQL($_POST['ToDate']) . "'";

} elseif ($_POST['CategoryID']!='All' AND $_POST['Location']=='All') {
	$sql= "SELECT InvoiceNo, OrderDeliveryDifferencesLog.OrderNo, OrderDeliveryDifferencesLog.StockID, StockMaster.Description, QuantityDiff, TranDate, OrderDeliveryDifferencesLog.DebtorNo, OrderDeliveryDifferencesLog.Branch FROM OrderDeliveryDifferencesLog INNER JOIN StockMaster ON OrderDeliveryDifferencesLog.StockID=StockMaster.StockID INNER JOIN DebtorTrans ON OrderDeliveryDifferencesLog.InvoiceNo=DebtorTrans.TransNo AND DebtorTrans.Type=10 AND TranDate >='" . FormatDateForSQL($_POST['FromDate']) . "' AND TranDate <='" . FormatDateForSQL($_POST['ToDate']) . "' AND CategoryID='" . $_POST['CategoryID'] ."'";

} elseif ($_POST['CategoryID']=='All' AND $_POST['Location']!='All') {
	$sql = "SELECT InvoiceNo, OrderDeliveryDifferencesLog.OrderNo, OrderDeliveryDifferencesLog.StockID, StockMaster.Description, QuantityDiff, TranDate, OrderDeliveryDifferencesLog.DebtorNo, OrderDeliveryDifferencesLog.Branch FROM OrderDeliveryDifferencesLog INNER JOIN StockMaster ON OrderDeliveryDifferencesLog.StockID=StockMaster.StockID INNER JOIN DebtorTrans ON OrderDeliveryDifferencesLog.InvoiceNo=DebtorTrans.TransNo INNER JOIN SalesOrders ON OrderDeliveryDifferencesLog.OrderNo=SalesOrders.OrderNo WHERE DebtorTrans.Type=10 AND SalesOrders.FromStkLoc='". $_POST['Location'] . "' AND TranDate >='" . FormatDateForSQL($_POST['FromDate']) . "' AND TranDate <='" . FormatDateForSQL($_POST['ToDate']) . "'";

} elseif ($_POST['CategoryID']!='All' AND $_POST['location']!='Áll'){

	$sql = "SELECT InvoiceNo, OrderDeliveryDifferencesLog.OrderNo, OrderDeliveryDifferencesLog.StockID, StockMaster.Description, QuantityDiff, TranDate, OrderDeliveryDifferencesLog.DebtorNo, OrderDeliveryDifferencesLog.Branch FROM OrderDeliveryDifferencesLog INNER JOIN StockMaster ON OrderDeliveryDifferencesLog.StockID=StockMaster.StockID INNER JOIN DebtorTrans ON OrderDeliveryDifferencesLog.InvoiceNo=DebtorTrans.TransNo AND DebtorTrans.Type=10 INNER JOIN SalesOrders ON OrderDeliveryDifferencesLog.OrderNo = SalesOrders.OrderNo WHERE SalesOrders.FromStkLoc='" . $_POST['Location'] . "' AND CategoryID='" . $_POST['CategoryID'] . "' AND TranDate >='" . FormatDateForSQL($_POST['FromDate']) . "' AND TranDate <= '" . FormatDateForSQL($_POST['ToDate']) . "'";


}

$Result=DB_query($sql,$db);

if (DB_error_no($db)!=0){
	include("includes/header.inc");
	echo "<BR>" . _("An error occurred getting the variances between deliveries and orders");
	if ($debug==1){
		echo "<BR>" . _("The SQL used to get the variances between deliveries and orders (that failed) was:") . "<BR>$SQL";
	}
	include ("includes/footer.inc");
	exit;
} elseif (DB_num_rows($Result)==0){
  	include("includes/header.inc");
	echo "<BR>" . _("There were no variances between deliveries and orders found in the database within the period from ") . $_POST['FromDate'] . _(" to ") . $_POST['ToDate'] . _(". Please try again selecting a different date range.");
	if ($debug==1) {
		echo _("The SQL that returned no rows was:") . "<BR>" . $SQL;
	}
	include("includes/footer.inc");
	exit;
}

$CompanyRecord = ReadInCompanyRecord($db);

include("includes/PDFStarter_ros.inc");

/*PDFStarter_ros.inc has all the variables for page size and width set up depending on the users default preferences for paper size */

$pdf->addinfo('Title',_('Variances Between Deliveries and Orders'));
$pdf->addinfo('Subject',_('Variances Between Deliveries and Orders from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate']);

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
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,_('Total number of differences') . ' ' . number_format($TotalDiffs), 'left');

if ($_POST['CategoryID']=='All' AND $_POST['Location']=='All'){
	$sql = "SELECT Count(OrderNo) FROM SalesOrderDetails INNER JOIN DebtorTrans ON SalesOrderDetails.OrderNo=DebtorTrans.Order_ WHERE DebtorTrans.TranDate>='" . FormatDateForSQL($_POST['FromDate']) . "' AND DebtorTrans.TranDate <='" . FormatDateForSQL($_POST['ToDate']) . "'";

} elseif ($_POST['CategoryID']!='All' AND $_POST['Location']=='All') {
	$sql = "SELECT Count(OrderNo) FROM SalesOrderDetails INNER JOIN DebtorTrans ON SalesOrderDetails.OrderNo=DebtorTrans.Order_ INNER JOIN StockMaster ON SalesOrderDetails.StkCode=StockMaster.StockID WHERE DebtorTrans.TranDate>='" . FormatDateForSQL($_POST['FromDate']) . "' AND DebtorTrans.TranDate <='" . FormatDateForSQL($_POST['ToDate']) . "' AND CategoryID='" . $_POST['CategoryID'] . "'";

} elseif ($_POST['CategoryID']=='All' AND $_POST['Location']!='All'){

	$sql = "SELECT Count(SalesOrderDetails.OrderNo) FROM SalesOrderDetails INNER JOIN DebtorTrans ON SalesOrderDetails.OrderNo=DebtorTrans.Order_ INNER JOIN SalesOrders ON SalesOrderDetails.OrderNo = SalesOrders.OrderNo WHERE DebtorTrans.TranDate>='". FormatDateForSQL($_POST['FromDate']) . "' AND DebtorTrans.TranDate <='" . FormatDateForSQL($_POST['ToDate']) . "' AND FromStkLoc='" . $_POST['Location'] . "'";

} elseif ($_POST['CategoryID'] !='All' AND $_POST['Location'] !='All'){

	$sql = "SELECT Count(SalesOrderDetails.OrderNo) FROM SalesOrderDetails INNER JOIN DebtorTrans ON SalesOrderDetails.OrderNo=DebtorTrans.Order_ INNER JOIN SalesOrders ON SalesOrderDetails.OrderNo = SalesOrders.OrderNo INNER JOIN StockMaster ON SalesOrderDetails.StkCode = StockMaster.StockID WHERE SalesOrders.FromStkLoc ='" . $_POST['Location'] . "' AND CategoryID='" . $_POST['CategoryID'] . "' AND TranDate >='" . FormatDateForSQL($_POST['FromDate']) . "' AND TranDate <= '" . FormatDateForSQL($_POST['ToDate']) . "'";

}
$Errmsg = _("Could not retrieve the count of sales order lines in the period under review");
$result = DB_query($sql,$db,$ErrMsg);


$myrow=DB_fetch_row($result);
$YPos-=$line_height;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,_('Total number of order lines') . ' ' . number_format($myrow[0]), 'left');

$YPos-=$line_height;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,_('DIFOT') . ' ' . number_format((1-($TotalDiffs/$myrow[0])) * 100,2) . "%", 'left');


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
	$mail->setText(_('Please find herewith delivery differences report from') . ' ' . $_POST['FromDate'] .  ' '. _('to') . ' ' . $_POST['ToDate']);
	$mail->addAttachment($attachment, 'DeliveryDifferences.pdf', 'application/pdf');
	$mail->setFrom(array('$CompanyName <' . $CompanyRecord["Email"] .'>'));

	/* $DelDiffsRecipients defined in config.php */
	$result = $mail->send($DelDiffsRecipients);
}

?>

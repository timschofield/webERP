<?php

/*These variables need to be modified to set up for ftp of files to a radio beacon ftp server */

$StockLocation ="BL";
$RadioBeaconHomeDir = "/home/RadioBeacon";
$FileCounter = "/home/RadioBeacon/FileCounter";
$FilePrefix = "ORDXX";
$ftp_server = "192.168.2.2";
$ftp_user_name = "RadioBeacon ftp server user name";
$ftp_user_pass = "Radio Beacon remote ftp server password";


$title="FTP order to Radio Beacon";
$PageSecurity = 2;
include("includes/session.inc");
include("includes/header.inc");

include("includes/SQL_CommonFunctions.inc");
include("includes/DateFunctions.inc");


/*Logic should allow entry of an order number which returns
some details of the order for confirming before producing the file for ftp */

$SQL = "SELECT SalesOrders.OrderNo, DebtorsMaster.Name, CustBranch.BrName, SalesOrders.CustomerRef, SalesOrders.OrdDate, SalesOrders.DeliverTo, SalesOrders.DeliveryDate, Sum(SalesOrderDetails.UnitPrice*SalesOrderDetails.Quantity*(1-SalesOrderDetails.DiscountPercent)) AS OrderValue, DatePackingSlipPrinted, PrintedPackingSlip FROM SalesOrders, SalesOrderDetails, DebtorsMaster, CustBranch WHERE SalesOrders.OrderNo = SalesOrderDetails.OrderNo AND SalesOrders.DebtorNo = DebtorsMaster.DebtorNo AND DebtorsMaster.DebtorNo = CustBranch.DebtorNo AND SalesOrderDetails.Completed=0 AND SalesOrders.FromStkLoc = '". $StockLocation . "' GROUP BY SalesOrders.OrderNo, SalesOrders.DebtorNo, SalesOrders.BranchCode, SalesOrders.CustomerRef, SalesOrders.OrdDate, SalesOrders.DeliverTo ";


$SalesOrdersResult = DB_query($SQL,$db);

if (DB_error_no($db) !=0) {
       echo "No orders were returned by the SQL because - " . DB_error_msg($db);
       echo "<BR>$SQL";
}

/*show a table of the orders returned by the SQL */

echo "<TABLE CELLPADDING=2 COLSPAN=7 WIDTH=100%><TR><TD BGCOLOR =#800000><FONT SIZE=2 COLOR=#ffffff>Modify</FONT></TD><TD BGCOLOR =#800000><FONT SIZE=2 COLOR=#ffffff>Send to<BR>Radio Beacon</FONT></TD><TD BGCOLOR =#800000><FONT SIZE=2 COLOR=#ffffff>Customer</FONT></TD><TD BGCOLOR =#800000><FONT SIZE=2 COLOR=#ffffff>Branch</FONT></TD><TD BGCOLOR =#800000><FONT SIZE=2 COLOR=#ffffff>Cust Order #</FONT></TD><TD BGCOLOR =#800000><FONT SIZE=2 COLOR=#ffffff>Order Date</FONT></TD><TD BGCOLOR =#800000><FONT SIZE=2 COLOR=#ffffff>Req Del Date</FONT></TD><TD BGCOLOR =#800000><FONT SIZE=2 COLOR=#ffffff>Delivery To</FONT></TD><TD BGCOLOR =#800000><FONT SIZE=2 COLOR=#ffffff>Order Total</FONT></TD><TD BGCOLOR =#800000><FONT SIZE=2 COLOR=#ffffff>Last Send</FONT></TD></TR>";

$j = 1;
$k=0; //row colour counter
while ($myrow=DB_fetch_array($SalesOrdersResult)) {
       if ($k==1){
              echo "<tr bgcolor='#CCCCCC'>";
              $k=0;
       } else {
              echo "<tr bgcolor='#EEEEEE'>";
              $k=1;
       }

       $FTPDispatchNote = $_SERVER['PHP_SELF'] . "?OrderNo=" . $myrow["OrderNo"];
       $FormatedDelDate = ConvertSQLDate($myrow["DeliveryDate"]);
       $FormatedOrderDate = ConvertSQLDate($myrow["OrdDate"]);
       $FormatedOrderValue = number_format($myrow["OrderValue"],2);
       $FormatedDateLastSent = ConvertSQLDate($myrow["DatePackingSlipPrinted"]);
       $ModifyPage = $rootpath . "SelectOrderItems.php?ModifyOrderNumber=" . $myrow["OrderNo"];

       if ($myrow["PrintedPackingSlip"] ==1){
              printf("<td><FONT SIZE=2><A HREF='%s'>%s</A></FONT></td><td><FONT COLOR=RED SIZE=2>Already<BR>Sent</FONT></td><td><FONT SIZE=2>%s</FONT></td><td><FONT SIZE=2>%s</FONT></td><td><FONT SIZE=2>%s</FONT></td><td><FONT SIZE=2>%s</FONT></td><td><FONT SIZE=2>%s</FONT></td><td><FONT SIZE=2>%s</FONT></td><td ALIGN=RIGHT><FONT SIZE=2>%s</FONT></td><td><FONT SIZE=2>%s</FONT></td></tr>", $ModifyPage, $myrow["OrderNo"], $myrow["Name"], $myrow["BrName"], $myrow["CustomerRef"],$FormatedOrderDate,$FormatedDelDate, $myrow["DeliverTo"], $FormatedOrderValue, $FormatedDateLastSent);
       } else {
              printf("<td><FONT SIZE=2><A HREF='%s'>%s</A></FONT></td><td><FONT SIZE=2><A HREF='%s'>Send</A></FONT></td><td><FONT SIZE=2>%s</FONT></td><td><FONT SIZE=2>%s</FONT></td><td><FONT SIZE=2>%s</FONT></td><td><FONT SIZE=2>%s</FONT></td><td><FONT SIZE=2>%s</FONT></td><td><FONT SIZE=2>%s</FONT></td><td ALIGN=RIGHT><FONT SIZE=2>%s</FONT></td><td><FONT SIZE=2>%s</FONT></td></tr>", $ModifyPage, $myrow["OrderNo"], $FTPDispatchNote, $myrow["Name"], $myrow["BrName"], $myrow["CustomerRef"],$FormatedOrderDate,$FormatedDelDate, $myrow["DeliverTo"], $FormatedOrderValue, $FormatedDateLastSent);
       }
       $j++;
       If ($j == 12){
              $j=1;
              echo "<TR><TD BGCOLOR =#800000><FONT SIZE=2 COLOR=#ffffff>Modify</FONT></TD><TD BGCOLOR =#800000><FONT SIZE=2 COLOR=#ffffff>Send to<BR>Radio Beacon</FONT></TD><TD BGCOLOR =#800000><FONT SIZE=2 COLOR=#ffffff>Customer</FONT></TD><TD BGCOLOR =#800000><FONT SIZE=2 COLOR=#ffffff>Branch</FONT></TD><TD BGCOLOR =#800000><FONT SIZE=2 COLOR=#ffffff>Cust Order #</FONT></TD><TD BGCOLOR =#800000><FONT SIZE=2 COLOR=#ffffff>Order Date</FONT></TD><TD BGCOLOR =#800000><FONT SIZE=2 COLOR=#ffffff>Req Del Date</FONT></TD><TD BGCOLOR =#800000><FONT SIZE=2 COLOR=#ffffff>Delivery To</FONT></TD><TD BGCOLOR =#800000><FONT SIZE=2 COLOR=#ffffff>Order Total</FONT></TD><TD BGCOLOR =#800000><FONT SIZE=2 COLOR=#ffffff>Last Send</FONT></TD></TR>";
       }
//end of page full new headings if
}
//end of while loop

echo "</TABLE>";


if (isset($_GET['OrderNo'])){ /*An order has been selected for sending */

       $CompanyRecord = ReadInCompanyRecord ($db);
       if ($CompanyRecord==0){
              /*CompanyRecord will be 0 if the company information could not be retrieved */
              echo "<P>There was a problem retrieving the company information ensure that the company record is correctly set up";
              exit;
       }

       /*Now get the order header info */

       $sql = "SELECT SalesOrders.DebtorNo, CustomerRef, Comments, OrdDate, DeliveryDate, DeliverTo, DelAdd1, DelAdd2, DelAdd3, DelAdd4, ContactPhone, ContactEmail, Name, Address1, Address2, Address3, Address4, PrintedPackingSlip, DatePackingSlipPrinted FROM SalesOrders, DebtorsMaster WHERE SalesOrders.DebtorNo=DebtorsMaster.DebtorNo AND SalesOrders.FromStkLoc = '". $StockLocation . "' AND SalesOrders.OrderNo=" . $_GET['OrderNo'];
       $result=DB_query($sql,$db);
       if (DB_error_no($db)!=0) {
          echo "There was a problem retrieving the order header details for Order Number " . $_GET['OrderNo'] ." from the database. ";
          if ($debug==1){
              echo "The SQL used to get this information (that failed) was:<BR>$sql";
          }
          break;
          exit;
       }
       if (DB_num_rows($result)==1){ /*There is ony one order header returned */

          $myrow = DB_fetch_array($result);
          if ($myrow["PrintedPackingSlip"]==1){
             echo "<P>Order Number " . $_GET['OrderNo'] ." has previously been sent to Radio Beacon. It was sent on " . ConvertSQLDate($myrow["DatePackingSlipPrinted"]) . "<BR>To re-send the order with the balance not previously dispatched and invoiced the order must be modified to allow a reprint (or re-send).<BR>This check is there to ensure that duplication of dispatches to the customer are avoided.";
             echo "<P><A HREF='$rootpath/SelectOrderItems.php?ModifyOrderNumber=" . $_GET['OrderNo'] . "'>Modify the order to allow a re-send/reprint (Select Delivery Details)</A>";
             echo "<P><A HREF='$rootpath/index.php'>Back to the main menu</A>";
             include("includes/footer.inc");
             exit;
          }

          /*Now get the line items */
          $sql = "SELECT StkCode, Description, Quantity, Units, QtyInvoiced, UnitPrice FROM SalesOrderDetails, StockMaster WHERE SalesOrderDetails.StkCode=StockMaster.StockID AND SalesOrderDetails.OrderNo=" . $_GET['OrderNo'];
          $result=DB_query($sql,$db);

          if (DB_error_no($db)!=0) {
             echo "<BR>There was a problem retrieving the line details for order number " . $_GET['OrderNo'] . " from the database.";
             if ($debug==1){
                  echo "<BR>The SQL used to get this information (that failed) was:<BR>$sql";
             }
             exit;
          }

          if (DB_num_rows($result)>0){
          /*Yes there are line items to start the ball rolling creating the Header record - the PHRecord*/

          /*First get the unique send id for the file name held in a seperate file */
          /*Now  get the file information inorder to create the Radio Beacon format file */

              if (file_exists($FileCounter)){
                    $fCounter = file($FileCounter);
                    $FileNumber = intval($fCounter[0]);
                    if ($FileNumber < 999){
                       $FileNumber++;
                    } else {
                      $FileNumber =1;
                    }
              } else {
                    $FileNumber=1;
              }


              $fp = fopen($FileCounter,"w");
              fwrite($fp, $FileNumber);
              fclose ($fp);

              $PHRecord = "PH^^^" . $myrow["DebtorNo"] . "^" . $_GET['OrderNo'] . "^" . $FileNumber . "^" . $myrow["CustomerRef"] . "^^^^^";
              $PHRecord = $PHRecord . $myrow["DeliverTo"] . "^" . $myrow["DelAdd1"] . "^" . $myrow["DelAdd2"] . "^" . $myrow["DelAdd3"] . "^" . $myrow["DelAdd4"] . "^^^^";
              $PHRecord = $PHRecord . $myrow["ContactPhone"] . "^" . $myrow["Name"] . "^" . $myrow["Address1"] . "^" . $myrow["Address2"] . "^" .$myrow["Address3"] . "^" .$myrow["Address4"] . "^^^";
              $PHRecord = $PHRecord . $myrow["DeliveryDate"] . "^^^^^^^" . $myrow["OrdDate"] . "^^^^^^DX^^^^^^^^^^^^^" . $CompanyName . "^" . $CompanyRecord["RegOffice1"] . "^" . $CompanyRecord["RegOffice2"] . "^";
              $PHRecord = $PHRecord . $CompanyRecord["RegOffice3"] . "^^^^^^^N^N^^H^^^^^^" . $myrow["DeliveryDate"] . "^^^^^^^" . $myrow["ContactPhone"] . "^" . $myrow["ContactEmail"] . "^^^^^^^^^^^^^^^^^^^^^^^^^^\n";

              $PDRec = array();
              $LineCounter =0;

              while ($myrow2=DB_fetch_array($result)){

                     $PickQty = $myrow2["Quantity"]- $myrow2["QtyInvoiced"];
                     $PDRec[$LineCounter] = "PD^^^" . $myrow["DebtorNo"] . "^" . $_GET['OrderNo'] . "^" . $FileNumber . "^^^^^^^" . $myrow2["StkCode"] . "^^" . $myrow2["Description"] . "^1^^^" . $myrow2["Quantity"] . "^" . $PickQty . "^^^^^^^^^^^^^^DX^^^^^^^^^^^^^^1000000000^" . $myrow["CustomerRef"] . "^^^^^^^^^^^^^^^^^^^^^^";
                     $LineCounter++;
              }

              /*the file number is used as an integer to uniquely identify multiple sendings of the order
              for back orders dispatched later */
              if ($FileNumber<10){
                  $FileNumber = "00" . $FileNumber;
              } elseif ($FileNumber <100){
                  $FileNumber = "0" . $FileNumber;
              }
              $FileName = $RadioBeaconHomeDir . "/" . $FilePrefix .  $FileNumber . ".txt";
              $fp = fopen($FileName, "w");

              fwrite($fp, $PHRecord);

              foreach ($PDRec AS $PD) {
                      fwrite($fp, $PD);
              }
              fclose($fp);

              echo "<P>FTP Connection progress .....<BR>";
              // set up basic connection
              $conn_id = ftp_connect("$ftp_server"); // login with username and password
              $login_result = ftp_login($conn_id, "$ftp_user_name", "$ftp_user_pass"); // check connection
              if ((!$conn_id) || (!$login_result)) {
                  echo "<BR>Ftp connection has failed!";
                  echo "<BR>Attempted to connect to $ftp_server for user $ftp_user_name";
                  die;
              } else {
                  echo "<BR>Connected to Radio Beacon FTP server at $ftp_server, with user name $ftp_user_name";
              } // upload the file
              $upload = ftp_put($conn_id, $FilePrefix .  $FileNumber . ".txt", $FileName, FTP_ASCII); // check upload status
              if (!$upload) {
                   echo "FTP upload has failed!";
                   exit;
              } else {
                   echo "<BR>Uploaded $FileName to $ftp_server";
              } // close the FTP stream
              ftp_quit($conn_id);

             /* Update the order printed flag to prevent double sendings */
             $sql = "UPDATE SalesOrders SET PrintedPackingSlip=1, DatePackingSlipPrinted='" . Date('Y-m-d') . "' WHERE SalesOrders.OrderNo=" . $_GET['OrderNo'];
             $result = DB_query($sql,$db);

              echo "<P>Order Number " . $_GET['OrderNo'] . " has been sent via FTP to Radio Beacon a copy of the file that was sent is held on the server at:<BR>" . $FileName;

       } else { /*perhaps several order headers returned or none (more likely) */

         echo "<P>The order " . $_GET['OrderNo'] . " for dispatch via Radio Beacon could not be retrieved. Perhaps it is set to be dispatched from a different stock location ?";

       }
    } /*there are line items outstanding for dispatch */

} /*end of if page called with a OrderNo - OrderNo*/

include("includes/footer.inc");
?>

<?php
/* $Revision: 1.8 $ */

/*Variables required to configure this script must be set in config.php */

$PageSecurity = 2;
include('includes/session.inc');
$title=_('FTP order to Radio Beacon');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');


/*Logic should allow entry of an order number which returns
some details of the order for confirming before producing the file for ftp */

$SQL = "SELECT salesorders.orderno,
		debtorsmaster.name,
		custbranch.brname,
		salesorders.customerref,
		salesorders.orddate,
		salesorders.deliverto,
		salesorders.deliverydate,
		sum(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) as ordervalue,
		datepackingslipprinted,
		printedpackingslip
	FROM salesorders,
		salesorderdetails,
		debtorsmaster,
		custbranch
	WHERE salesorders.orderno = salesorderdetails.orderno
	AND salesorders.debtorno = debtorsmaster.debtorno
	AND debtorsmaster.debtorno = custbranch.debtorno
	AND salesorderdetails.completed=0
	AND salesorders.fromstkloc = '". $_SESSION['RadioBeaconStockLocation'] . "'
	GROUP BY salesorders.orderno,
		salesorders.debtorno,
		salesorders.branchcode,
		salesorders.customerref,
		salesorders.orddate,
		salesorders.deliverto";

$ErrMsg = _('No orders were returned because');
$SalesOrdersResult = DB_query($SQL,$db,$ErrMsg);

/*show a table of the orders returned by the SQL */

echo '<TABLE CELLPADDING=2 COLSPAN=7 WIDTH=100%>';
$TableHeader =	'<TR>
		<TD class=tableheader>' . _('Modify') . '</TD>
		<TD class=tableheader>' . _('Send to') . '<BR>' . _('Radio Beacon') . '</TD>
		<TD class=tableheader>' . _('Customer') . '</TD>
		<TD class=tableheader>' . _('Branch') . '</TD>
		<TD class=tableheader>' . _('Cust Order') . ' #</TD>
		<TD class=tableheader>' . _('Order Date') . '</TD>
		<TD class=tableheader>' . _('Req Del Date') . '</TD>
		<TD class=tableheader>' . _('Delivery To') . '</TD>
		<TD class=tableheader>' . _('Order Total') . '</TD>
		<TD class=tableheader>' . _('Last Send') . '</TD>
		</TR>';

echo $TableHeader;

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

       $FTPDispatchNote = $_SERVER['PHP_SELF'] . '?' . SID . '&OrderNo=' . $myrow['orderno'];
       $FormatedDelDate = ConvertSQLDate($myrow['deliverydate']);
       $FormatedOrderDate = ConvertSQLDate($myrow['orddate']);
       $FormatedOrderValue = number_format($myrow['ordervalue'],2);
       $FormatedDateLastSent = ConvertSQLDate($myrow['datepackingslipprinted']);
       $ModifyPage = $rootpath . 'SelectOrderItems.php?' . SID . '&ModifyOrderNumber=' . $myrow['orderno'];

       if ($myrow['printedpackingslip'] ==1){
              printf("<td><FONT SIZE=2><A HREF='%s'>%s</A></FONT></td>
	      		<td><FONT COLOR=RED SIZE=2>" . _('Already') . '<BR>' . _('Sent') . "</FONT></td>
			<td><FONT SIZE=2>%s</FONT></td>
			<td><FONT SIZE=2>%s</FONT></td>
			<td><FONT SIZE=2>%s</FONT></td>
			<td><FONT SIZE=2>%s</FONT></td>
			<td><FONT SIZE=2>%s</FONT></td>
			<td><FONT SIZE=2>%s</FONT></td>
			<td ALIGN=RIGHT><FONT SIZE=2>%s</FONT></td>
			<td><FONT SIZE=2>%s</FONT></td></tr>",
			$ModifyPage,
			$myrow['orderno'],
			$myrow['name'],
			$myrow['brname'],
			$myrow['customerref'],
			$FormatedOrderDate,
			$FormatedDelDate,
			$myrow['deliverto'],
			$FormatedOrderValue,
			$FormatedDateLastSent);
       } else {
              printf("<td><FONT SIZE=2><A HREF='%s'>%s</A></FONT></td>
	      		<td><FONT SIZE=2><A HREF='%s'>" . _('Send') . "</A></FONT></td>
			<td><FONT SIZE=2>%s</FONT></td>
			<td><FONT SIZE=2>%s</FONT></td>
			<td><FONT SIZE=2>%s</FONT></td>
			<td><FONT SIZE=2>%s</FONT></td>
			<td><FONT SIZE=2>%s</FONT></td>
			<td><FONT SIZE=2>%s</FONT></td>
			<td ALIGN=RIGHT><FONT SIZE=2>%s</FONT></td>
			<td><FONT SIZE=2>%s</FONT></td></tr>",
			$ModifyPage,
			$myrow['orderno'],
			$FTPDispatchNote,
			$myrow['name'],
			$myrow['brname'],
			$myrow['customerref'],
			$FormatedOrderDate,
			$FormatedDelDate,
			$myrow['deliverto'],
			$FormatedOrderValue,
			$FormatedDateLastSent);
       }
       $j++;
       If ($j == 12){
              $j=1;
              echo $TableHeader;
       }
//end of page full new headings if
}
//end of while loop

echo '</TABLE>';


if (isset($_GET['OrderNo'])){ /*An order has been selected for sending */

       if ($_SESSION['CompanyRecord']==0){
              /*CompanyRecord will be 0 if the company information could not be retrieved */
              prnMsg(_('There was a problem retrieving the company information ensure that the company record is correctly set up'),'error');
	      include('includes/footer.inc');
	      exit;
       }

       /*Now get the order header info */

       $sql = "SELECT salesorders.debtorno,
       			customerref,
			comments,
			orddate,
			deliverydate,
			deliverto,
			deladd1,
			deladd2,
			deladd3,
			deladd4,
			deladd5,
			deladd6,
			contactphone,
			contactemail,
			name,
			address1,
			address2,
			address3,
			address4,
			address5,
			address6,
			printedpackingslip,
			datepackingslipprinted
		FROM salesorders,
			debtorsmaster
		WHERE salesorders.debtorno=debtorsmaster.debtorno
		AND salesorders.fromstkloc = '". $_SESSION['RadioBeaconStockLocation'] . "'
		AND salesorders.orderno=" . $_GET['OrderNo'];


       $ErrMsg = _('There was a problem retrieving the order header details for Order Number') . ' ' . $_GET['OrderNo'] . ' ' . _('from the database');
	$result=DB_query($sql,$db,$ErrMsg);

       if (DB_num_rows($result)==1){ /*There is ony one order header returned */

          $myrow = DB_fetch_array($result);
          if ($myrow['printedpackingslip']==1){
             prnMsg(_('Order Number') . ' ' . $_GET['OrderNo'] . ' ' . _('has previously been sent to Radio Beacon') . '. ' . _('It was sent on') . ' ' . ConvertSQLDate($myrow['datepackingslipprinted']) . '<BR>' . _('To re-send the order with the balance not previously dispatched and invoiced the order must be modified to allow a reprint (or re-send)') . '.<BR>' . _('This check is there to ensure that duplication of dispatches to the customer are avoided'),'warn');
             echo "<P><A HREF='$rootpath/SelectOrderItems.php?" . SID . "&ModifyOrderNumber=" . $_GET['OrderNo'] . "'>" . _('Modify the order to allow a re-send or reprint') . ' (' . _('Select Delivery Details') . ')' . '</A>';
             echo "<P><A HREF='$rootpath/index.php'>" . _('Back to the menu') . '</A>';
             include('includes/footer.inc');
             exit;
          }

          /*Now get the line items */
          $sql = "SELECT stkcode,
	  		description,
			quantity,
			units,
			qtyinvoiced,
			unitprice
		FROM salesorderdetails,
			stockmaster
		WHERE salesorderdetails.stkcode=stockmaster.stockid
		AND salesorderdetails.orderno=" . $_GET['OrderNo'];

          $ErrMsg = _('There was a problem retrieving the line details for order number') . ' ' . $_GET['OrderNo'] . ' ' . _('from the database because');
	  $result=DB_query($sql,$db, $ErrMsg);

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


              $fp = fopen($FileCounter,'w');
              fwrite($fp, $FileNumber);
              fclose ($fp);

              $PHRecord = 'PH^^^' . $myrow['debtorno'] . '^' . $_GET['OrderNo'] . '^' . $FileNumber . '^' . $myrow['customerref'] . '^^^^^';
              $PHRecord = $PHRecord . $myrow['deliverto'] . '^' . $myrow['deladd1'] . '^' . $myrow['deladd2'] . '^' . $myrow['deladd3'] . '^' . $myrow['deladd4'] . '^' . $myrow['deladd5'] . '^' . $myrow['deladd6'] . '^^^^';
              $PHRecord = $PHRecord . $myrow['contactphone'] . '^' . $myrow['name'] . '^' . $myrow['address1'] . '^' . $myrow['address2'] . '^' .$myrow['address3'] . '^' .$myrow['address4'] . '^' .$myrow['address5'] . '^' .$myrow['address6'] . '^^^';
              $PHRecord = $PHRecord . $myrow['deliverydate'] . '^^^^^^^' . $myrow['orddate'] . '^^^^^^DX^^^^^^^^^^^^^' . $_SESSION['CompanyRecord']['coyname'] . '^' . $_SESSION['CompanyRecord']['regoffice1'] . '^' . $_SESSION['CompanyRecord']['regoffice2'] . '^';
              $PHRecord = $PHRecord . $_SESSION['CompanyRecord']['regoffice3'] . '^' . $_SESSION['CompanyRecord']['regoffice4'] . '^' . $_SESSION['CompanyRecord']['regoffice5'] . '^' . $_SESSION['CompanyRecord']['regoffice6'] . '^';
              $PHRecord = $PHRecord . '^^^^^^^N^N^^H^^^^^^' . $myrow['deliverydate'] . '^^^^^^^' . $myrow['contactphone'] . '^' . $myrow['contactemail'] . '^^^^^^^^^^^^^^^^^^^^^^^^^^\n';

              $PDRec = array();
              $LineCounter =0;

              while ($myrow2=DB_fetch_array($result)){

                     $PickQty = $myrow2['quantity']- $myrow2['qtyinvoiced'];
                     $PDRec[$LineCounter] = 'PD^^^' . $myrow['debtorno'] . '^' . $_GET['OrderNo'] . '^' . $FileNumber . '^^^^^^^' . $myrow2['stkcode'] . '^^' . $myrow2['description'] . '^1^^^' . $myrow2['quantity'] . '^' . $PickQty . '^^^^^^^^^^^^^^DX^^^^^^^^^^^^^^1000000000^' . $myrow['customerref'] . '^^^^^^^^^^^^^^^^^^^^^^';
                     $LineCounter++;
              }

              /*the file number is used as an integer to uniquely identify multiple sendings of the order
              for back orders dispatched later */
              if ($FileNumber<10){
                  $FileNumber = '00' . $FileNumber;
              } elseif ($FileNumber <100){
                  $FileNumber = '0' . $FileNumber;
              }
              $FileName = $_SESSION['RadioBeaconHomeDir'] . '/' . $FilePrefix .  $FileNumber . '.txt';
              $fp = fopen($FileName, 'w');

              fwrite($fp, $PHRecord);

              foreach ($PDRec AS $PD) {
                      fwrite($fp, $PD);
              }
              fclose($fp);

              echo '<P>' . _('FTP Connection progress') . ' .....';
              // set up basic connection
              $conn_id = ftp_connect($_SESSION['RadioBeaconFTP_server']); // login with username and password
              $login_result = ftp_login($conn_id, $_SESSION['RadioBeaconFTP_user_name'], $_SESSION['RadioBeaconFTP_user_pass']); // check connection
              if ((!$conn_id) || (!$login_result)) {
                  echo '<BR>' . _('Ftp connection has failed');
                  echo '<BR>' . _('Attempted to connect to') . ' ' . $_SESSION['RadioBeaconFTP_server'] . ' ' . _('for user') . ' ' . $_SESSION['RadioBeaconFTP_user_name'];
                  die;
              } else {
                  echo '<BR>' . _('Connected to Radio Beacon FTP server at') . ' ' . $_SESSION['RadioBeaconFTP_server'] . ' ' . _('with user name') . ' ' . $_SESSION['RadioBeaconFTP_user_name'];
              } // upload the file
              $upload = ftp_put($conn_id, $FilePrefix .  $FileNumber . '.txt', $FileName, FTP_ASCII); // check upload status
              if (!$upload) {
                   prnMsg(_('FTP upload has failed'),'success');
                   exit;
              } else {
                   echo '<BR>' . _('Uploaded') . ' ' . $FileName . ' ' . _('to') . ' ' . $_SESSION['RadioBeaconFTP_server'];
              } // close the FTP stream
              ftp_quit($conn_id);

             /* Update the order printed flag to prevent double sendings */
             $sql = "UPDATE salesorders SET printedpackingslip=1, datepackingslipprinted='" . Date('Y-m-d') . "' WHERE salesorders.orderno=" . $_GET['OrderNo'];
             $result = DB_query($sql,$db);

              echo '<P>' . _('Order Number') . ' ' . $_GET['OrderNo'] . ' ' . _('has been sent via FTP to Radio Beacon a copy of the file that was sent is held on the server at') . '<BR>' . $FileName;

       } else { /*perhaps several order headers returned or none (more likely) */

         echo '<P>' . _('The order') . ' ' . $_GET['OrderNo'] . ' ' . _('for dispatch via Radio Beacon could not be retrieved') . '. ' . _('Perhaps it is set to be dispatched from a different stock location');

       }
    } /*there are line items outstanding for dispatch */

} /*end of if page called with a OrderNo - OrderNo*/

include('includes/footer.inc');
?>
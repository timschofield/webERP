<?php

$PageSecurity = 2;

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
//Get Out if we have no order number to work with
If (!isset($_GET['TransNo']) OR $_GET['TransNo']==""){
        $title = _('Select Order To Print');
        include('includes/header.inc');
        echo '<div align=center><br><br><br>';
        prnMsg( _('Select an Order Number to Print before calling this page') , 'error');
        echo '<BR><BR><BR><table class="table_index"><tr><td class="menu_group_item">
                <li><a href="'. $rootpath . '/SelectSalesOrder.php?'. SID .'">' . _('Outstanding Sales Orders') . '</a></li>
                <li><a href="'. $rootpath . '/SelectCompletedOrder.php?'. SID .'">' . _('Completed Sales Orders') . '</a></li>
                </td></tr></table></DIV><BR><BR><BR>';
        include('includes/footer.inc');
        exit();
}

$MailTo 		= $_GET['EMail'];
$headers 	= 'From: Bethany Manufacturing <sales@bethanymfg.com>' . "\n";
$headers   .=  "MIME-Version: 1.0\n"
  	."Content-Type: text/html; charset=\"iso-8859-1\"\n";

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
		salesorders.deliverydate
	FROM salesorders,
		debtorsmaster,
		shippers,
		locations
	WHERE salesorders.debtorno=debtorsmaster.debtorno
	AND salesorders.shipvia=shippers.shipper_id
	AND salesorders.fromstkloc=locations.loccode
	AND salesorders.orderno=" . $_GET['TransNo'];

$result=DB_query($sql,$db, $ErrMsg);

//If there are no rows, there's a problem.
if (DB_num_rows($result)==0){
        $title = _('Print Packing Slip Error');
        include('includes/header.inc');
         echo '<div align=center><br><br><br>';
        prnMsg( _('Unable to Locate Order Number') . ' : ' . $_GET['TransNo'] . ' ', 'error');
        echo '<BR><BR><BR><table class="table_index"><tr><td class="menu_group_item">
                <li><a href="'. $rootpath . '/SelectSalesOrder.php?'. SID .'">' . _('Outstanding Sales Orders') . '</a></li>
                <li><a href="'. $rootpath . '/SelectCompletedOrder.php?'. SID .'">' . _('Completed Sales Orders') . '</a></li>
                </td></tr></table></DIV><BR><BR><BR>';
        include('includes/footer.inc');
        exit();
} elseif (DB_num_rows($result)==1){ /*There is only one order header returned - thats good! */

        $myrow = DB_fetch_array($result);
        /* Place the deliver blind variable into a hold variable to used when
        producing the packlist */
        $DeliverBlind = $myrow['deliverblind'];
        $DeliveryDate = $myrow['salesorders.deliverydate'];
        if ($myrow['printedpackingslip']==1 AND ($_GET['Reprint']!='OK' OR !isset($_GET['Reprint']))){
                $title = _('Print Packing Slip Error');
                include('includes/header.inc');
                echo '<P>';
                prnMsg( _('The packing slip for order number') . ' ' . $_GET['TransNo'] . ' ' .
                        _('has previously been printed') . '. ' . _('It was printed on'). ' ' . ConvertSQLDate($myrow['datepackingslipprinted']) .
                        '<br>' . _('This check is there to ensure that duplicate packing slips are not produced and dispatched more than once to the customer'), 'warn' );
              echo '<P><A HREF="' . $rootpath . '/PrintCustOrder.php?' . SID . '&TransNo=' . $_GET['TransNo'] . '&Reprint=OK">'
                . _('Do a Re-Print') . ' (' . _('On Pre-Printed Stationery') . ') ' . _('Even Though Previously Printed') . '</A><P>' .
                '<A HREF="' . $rootpath. '/PrintCustOrder_generic.php?' . SID . '&TransNo=' . $_GET['TransNo'] . '&Reprint=OK">'. _('Do a Re-Print') . ' (' . _('Plain paper') . ' - ' . _('A4') . ' ' . _('landscape') . ') ' . _('Even Though Previously Printed'). '</A>';

                echo '<BR><BR><BR>';
                echo  _('Or select another Order Number to Print');
                echo '<table class="table_index"><tr><td class="menu_group_item">
                        <li><a href="'. $rootpath . '/SelectSalesOrder.php?'. SID .'">' . _('Outstanding Sales Orders') . '</a></li>
                        <li><a href="'. $rootpath . '/SelectCompletedOrder.php?'. SID .'">' . _('Completed Sales Orders') . '</a></li>
                        </td></tr></table></DIV><BR><BR><BR>';

                include('includes/footer.inc');
                exit;
        }//packing slip has been printed.
        $MailSubject = "Order Confirmation-Sales Order " .  $_GET['TransNo'] . " - Your PO " . 
                $myrow['customerref'] ;
}

/*retrieve the order details from the database to print */

/* Then there's an order to print and its not been printed already (or its been flagged for reprinting/ge_Width=807;
)
LETS GO */

$MailMessage =  "<html><head><title>Email Confirmation</title></head><body>" . 
                 "<Table cellpadding='2' cellspacing='2'><TR>" . 
                 "<td align='center' colspan='4'><H1>" . $_SESSION['CompanyRecord']['coyname'] . "</H1>" ;
$MailMessage =  $MailMessage .   "</td></tr>";
$MailMessage = $MailMessage . "<TR><td colspan='4'> <B>" . 
										$_SESSION['CompanyRecord']['regoffice1'] . "</TD></TR>";
$MailMessage = $MailMessage . "<TR><td colspan='4'> <B>" .										
										$_SESSION['CompanyRecord']['regoffice4'] . ",";
$MailMessage = $MailMessage . "<B>" .					
										$_SESSION['CompanyRecord']['regoffice5'] . "</TD></TR>";
$MailMessage = $MailMessage . "<TR><td colspan='4'> <B>" . 
										$_SESSION['CompanyRecord']['telephone'] . ' ' . _('Fax'). ': ' . 
										$_SESSION['CompanyRecord']['fax'] . "</TD></TR>";
$MailMessage = $MailMessage . "<TR><td colspan='4'> <B>" . 
										$_SESSION['CompanyRecord']['email'] . "<BR><BR><BR></TD></TR>";
$MailMessage = $MailMessage . "<Table><TR><TD align='center' colspan='4'> 
                 <h2> Order Acknowledgement</h2></TD></TR>";
$MailMessage = $MailMessage . "<TR><td align='center' colspan='4'> <B>Order Number " . 
                              $_GET['TransNo'] .  "</B><BR><BR><BR></td></tr>";
$MailMessage = $MailMessage . "<TR><td colspan='4'> <B>Delivered To:</B></TD></TR>";
$MailMessage = $MailMessage . "<TR><td colspan='4'> <B>" . 
										$myrow['deliverto'] . "</TD></TR>";
$MailMessage = $MailMessage . "<TR><td colspan='4'> <B>" . 
										$myrow['deladd1'] . "</TD></TR>";
If(strlen(trim($myrow['deladd2'])))
{
      $MailMessage = $MailMessage . "<TR><td> <B>" . $myrow['deladd2'] . "</TD></TR>";
      $MailMessage = $MailMessage . "<TR><td> <B>" . $myrow['deladd3'] . 
                              ' ' . $myrow['deladd4'] . ' ' . $myrow['deladd5']. "<BR><BR><BR></TD></tr>";
}
else
{
      $MailMessage = $MailMessage . "<TR><td> <B>" . $myrow['deladd3'] . ' ' . 
                              $myrow['deladd4'] . ' ' . $myrow['deladd5'] . "<BR><BR><BR></TD></tr>";
}
$MailMessage = $MailMessage . "</table><table border='1' width='50%'><TR>";
if($_REQUEST['POLine'] == 1){
	$MailMessage = $MailMessage . "	<td>PO Line</TD>";
}
	$MailMessage = $MailMessage . "<td>Stock Code</TD>
		<TD>Description</TD>
		<TD>Quantity Ordered</TD>
      		<TD>Due Date</TD></tr>";


	$sql = "SELECT salesorderdetails.stkcode, 
			stockmaster.description, 
			salesorderdetails.quantity, 
			salesorderdetails.qtyinvoiced, 
			salesorderdetails.unitprice,
			salesorderdetails.narrative,
			salesorderdetails.poline,
			salesorderdetails.itemdue
		FROM salesorderdetails INNER JOIN stockmaster
			ON salesorderdetails.stkcode=stockmaster.stockid
		WHERE salesorderdetails.orderno=" . $_GET['TransNo'] . "
		ORDER BY poline";
	$result=DB_query($sql,$db, $ErrMsg);
	$i=0;
	if (DB_num_rows($result)>0){

		while ($myrow2=DB_fetch_array($result)){

			$DisplayQty = number_format($myrow2['quantity'],0);
			$DisplayPrevDel = number_format($myrow2['qtyinvoiced'],0);
			$DisplayQtySupplied = number_format($myrow2['quantity'] - $myrow2['qtyinvoiced'],0);
         		$StkCode[$i] = $myrow2['stkcode'];
         		$DscCode[$i] = $myrow2['description'];
         		$QtyCode[$i] = $DisplayQty ;
         		$POLine[$i]  = $myrow2['poline'];
        		If($myrow2['itemdue'] =='')
        		{
         			$ItemDue[$i] = date('M d, Y',strtotime($DeliveryDate));
        		}
       			 else
        		{
        			$ItemDue[$i] = date('M d, Y',strtotime($myrow2['itemdue']));
        		}         		
			$MailMessage = $MailMessage . "<TR>";
			if($_REQUEST['POLine'] == 1){
				$MailMessage = $MailMessage . "<td align='right'>" . $POLine[$i] . "</TD>";
			}
			$MailMessage = $MailMessage . "<td>" . $myrow2['stkcode'] .
			                "</TD><TD>" . 
			                $myrow2['description'] . "</TD><TD align='right'>" . 
			                $DisplayQty . "</TD><TD align='center'>" . $ItemDue[$i]  . 			                
                 			 "</TD></tr>";

         $i = $i + 1;

		} //end while there are line items to print out

	} /*end if there are order details to show on the order*/
$MailMessage = $MailMessage . "</Table></body></html>";
// echo $MailMessage . "=mailMessage<BR>";
IF(mail( $MailTo, $MailSubject, $MailMessage, $headers )){
	echo " The following E-Mail was sent to $MailTo:";
}
?>
<html>
<head>
<title>Email Confirmation</title>
</head>
<body>
<Table width='60%'>
	<TR>
		<td align='center' colspan='4'> <?php echo "<IMG src='" . $rootpath . '/' . $_SESSION['LogoFile'] . "' alt='Logo'" .
                 "width='500' height='100' align='center' border='0'>" ?>
      		</td>
   	</tr>
	<TR>
		<TD align='center' colspan='4'> <h2> Order Acknowledgement</h2></TD>
	</TR>
 	<TR>
 		<td align='center' colspan='4'> <B>Order Number <?=$_GET['TransNo']?> </B><BR><BR><BR></td>
 	</tr>
 	<TR>
 		<td colspan='2' nowrap width="50%"> <B><?=$_SESSION['CompanyRecord']['coyname']?></B></TD>
 		<td colspan='2' nowrap width="50%"> <B>Delivered To:</B></TD>
 	</TR>
 	<TR>
 		<td colspan='2' nowrap width="50%"> <B><?=$_SESSION['CompanyRecord']['regoffice1']?> </B></TD>
 		<td colspan='2' nowrap width="50%"> <B><?=$myrow['deliverto']?></TD>
 	</TR>
  	<TR>
  		<td colspan='2' nowrap width="50%"> 
  			<B><?=$_SESSION['CompanyRecord']['regoffice4']?>,
				<?=$_SESSION['CompanyRecord']['regoffice5']?> </B>
		</TD>
		<td colspan='2' nowrap width="50%"> <B><?=$myrow['deladd1']?></TD>
	</TR>
 	<TR>
 		<td colspan='2' nowrap width="50%"> 
 			<B><?=$_SESSION['CompanyRecord']['telephone']?>  
 			Fax:<?=$_SESSION['CompanyRecord']['fax']?></B>
 		</TD>
 		<td nowrap width="50%"><B><?=$myrow['deladd2']?></TD>
 	</TR>
 	<TR>
 		<td colspan='2' nowrap width="50%"> 
 			<B><?=$_SESSION['CompanyRecord']['email']?><BR><BR><BR>
 		</TD> 		
     		<td nowrap width="50%">
       		<B><?=$myrow['deladd3']?> <?=$myrow['deladd4'] ?> <?=$myrow['deladd5']?><BR><BR><BR>
      		</TD>
 	</TR>
</table>
<table border='1' width='60%' cellpadding="2" cellspacing='2'>
	<TR>
<?
if($_REQUEST['POLine'] == 1){
?>	
		<td align="center">PO Line</TD>
<?
}
?>		
		<td align="center">Stock Code</TD>
		<TD align="center">Description</TD>
		<TD align="center">Quantity Ordered</TD>
      		<TD align="center">Due Date</TD>
   	</tr>
<?
For( $j=0; $j<$i; $j++)
{
?>
	<TR>
<?
	if($_REQUEST['POLine']){
?>	
		<td align='right'><?=$POLine[$j]?></TD>
<?
	}
?>		
		<td><?=$StkCode[$j]?></TD>
		<TD><?=$DscCode[$j]?></TD>
		<TD align="right"><?=$QtyCode[$j]?></TD>
      		<td align="center"><?=$ItemDue[$j]?></TD>
   	</tr>
<?
}
?>   
</table>
</body>
</html>
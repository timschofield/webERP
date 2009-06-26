<?php

$PageSecurity = 2;

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
//Get Out if we have no order number to work with
If (!isset($_GET['TransNo']) OR $_GET['TransNo']==""){
        $title = _('Select Order To Print');
        include('includes/header.inc');
        echo '<div class="centre"><br><br><br>';
        prnMsg( _('Select an Order Number to Print before calling this page') , 'error');
        echo '<br><br><br><table class="table_index"><tr><td class="menu_group_item">
                <li><a href="'. $rootpath . '/SelectSalesOrder.php?'. SID .'">' . _('Outstanding Sales Orders') . '</a></li>
                <li><a href="'. $rootpath . '/SelectCompletedOrder.php?'. SID .'">' . _('Completed Sales Orders') . '</a></li>
                </td></tr></table></div><br><br><br>';
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
         echo '<div class="centre"><br><br><br>';
        prnMsg( _('Unable to Locate Order Number') . ' : ' . $_GET['TransNo'] . ' ', 'error');
        echo '<br><br><br><table class="table_index"><tr><td class="menu_group_item">
                <li><a href="'. $rootpath . '/SelectSalesOrder.php?'. SID .'">' . _('Outstanding Sales Orders') . '</a></li>
                <li><a href="'. $rootpath . '/SelectCompletedOrder.php?'. SID .'">' . _('Completed Sales Orders') . '</a></li>
                </td></tr></table></div><br><br><br>';
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
                echo '<p>';
                prnMsg( _('The packing slip for order number') . ' ' . $_GET['TransNo'] . ' ' .
                        _('has previously been printed') . '. ' . _('It was printed on'). ' ' . ConvertSQLDate($myrow['datepackingslipprinted']) .
                        '<br>' . _('This check is there to ensure that duplicate packing slips are not produced and dispatched more than once to the customer'), 'warn' );
              echo '<p><a href="' . $rootpath . '/PrintCustOrder.php?' . SID . '&TransNo=' . $_GET['TransNo'] . '&Reprint=OK">'
                . _('Do a Re-Print') . ' (' . _('On Pre-Printed Stationery') . ') ' . _('Even Though Previously Printed') . '</a><p>' .
                '<a href="' . $rootpath. '/PrintCustOrder_generic.php?' . SID . '&TransNo=' . $_GET['TransNo'] . '&Reprint=OK">'. _('Do a Re-Print') . ' (' . _('Plain paper') . ' - ' . _('A4') . ' ' . _('landscape') . ') ' . _('Even Though Previously Printed'). '</a>';

                echo '<br><br><br>';
                echo  _('Or select another Order Number to Print');
                echo '<table class="table_index"><tr><td class="menu_group_item">
                        <li><a href="'. $rootpath . '/SelectSalesOrder.php?'. SID .'">' . _('Outstanding Sales Orders') . '</a></li>
                        <li><a href="'. $rootpath . '/SelectCompletedOrder.php?'. SID .'">' . _('Completed Sales Orders') . '</a></li>
                        </td></tr></table></div><br><br><br>';

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
                 "<Table cellpadding='2' cellspacing='2'><tr>" . 
                 "<td align='center' colspan='4'><H1>" . $_SESSION['CompanyRecord']['coyname'] . "</H1>" ;
$MailMessage =  $MailMessage .   "</td></tr>";
$MailMessage = $MailMessage . "<tr><td colspan='4'> <b>" . 
										$_SESSION['CompanyRecord']['regoffice1'] . "</td></tr>";
$MailMessage = $MailMessage . "<tr><td colspan='4'> <b>" .										
										$_SESSION['CompanyRecord']['regoffice4'] . ",";
$MailMessage = $MailMessage . "<b>" .					
										$_SESSION['CompanyRecord']['regoffice5'] . "</td></tr>";
$MailMessage = $MailMessage . "<tr><td colspan='4'> <b>" . 
										$_SESSION['CompanyRecord']['telephone'] . ' ' . _('Fax'). ': ' . 
										$_SESSION['CompanyRecord']['fax'] . "</td></tr>";
$MailMessage = $MailMessage . "<tr><td colspan='4'> <b>" . 
										$_SESSION['CompanyRecord']['email'] . "<br><br><br></td></tr>";
$MailMessage = $MailMessage . "<Table><tr><td align='center' colspan='4'> 
                 <h2> Order Acknowledgement</h2></td></tr>";
$MailMessage = $MailMessage . "<tr><td align='center' colspan='4'> <b>Order Number " . 
                              $_GET['TransNo'] .  "</b><br><br><br></td></tr>";
$MailMessage = $MailMessage . "<tr><td colspan='4'> <b>Delivered To:</b></td></tr>";
$MailMessage = $MailMessage . "<tr><td colspan='4'> <b>" . 
										$myrow['deliverto'] . "</td></tr>";
$MailMessage = $MailMessage . "<tr><td colspan='4'> <b>" . 
										$myrow['deladd1'] . "</td></tr>";
If(strlen(trim($myrow['deladd2'])))
{
      $MailMessage = $MailMessage . "<tr><td> <b>" . $myrow['deladd2'] . "</td></tr>";
      $MailMessage = $MailMessage . "<tr><td> <b>" . $myrow['deladd3'] . 
                              ' ' . $myrow['deladd4'] . ' ' . $myrow['deladd5']. "<br><br><br></td></tr>";
}
else
{
      $MailMessage = $MailMessage . "<tr><td> <b>" . $myrow['deladd3'] . ' ' . 
                              $myrow['deladd4'] . ' ' . $myrow['deladd5'] . "<br><br><br></td></tr>";
}
$MailMessage = $MailMessage . "</table><table border='1' width='50%'><tr>";
if($_REQUEST['POLine'] == 1){
	$MailMessage = $MailMessage . "	<td>PO Line</td>";
}
	$MailMessage = $MailMessage . "<td>Stock Code</td>
		<td>Description</td>
		<td>Quantity Ordered</td>
      		<td>Due Date</td></tr>";


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
			$MailMessage = $MailMessage . "<tr>";
			if($_REQUEST['POLine'] == 1){
				$MailMessage = $MailMessage . "<td align='right'>" . $POLine[$i] . "</td>";
			}
			$MailMessage = $MailMessage . "<td>" . $myrow2['stkcode'] .
			                "</td><td>" . 
			                $myrow2['description'] . "</td><td align='right'>" . 
			                $DisplayQty . "</td><td align='center'>" . $ItemDue[$i]  . 			                
                 			 "</td></tr>";

         $i = $i + 1;

		} //end while there are line items to print out

	} /*end if there are order details to show on the order*/
$MailMessage = $MailMessage . "</Table></body></html>";
// echo $MailMessage . "=mailMessage<br>";
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
	<tr>
		<td align='center' colspan='4'> <?php echo "<IMG src='" . $rootpath . '/' . $_SESSION['LogoFile'] . "' alt='Logo'" .
                 "width='500' height='100' align='center' border='0'>" ?>
      		</td>
   	</tr>
	<tr>
		<td align='center' colspan='4'> <h2> Order Acknowledgement</h2></td>
	</tr>
 	<tr>
 		<td align='center' colspan='4'> <b>Order Number <?=$_GET['TransNo']?> </b><br><br><br></td>
 	</tr>
 	<tr>
 		<td colspan='2' nowrap width="50%"> <b><?=$_SESSION['CompanyRecord']['coyname']?></b></td>
 		<td colspan='2' nowrap width="50%"> <b>Delivered To:</b></td>
 	</tr>
 	<tr>
 		<td colspan='2' nowrap width="50%"> <b><?=$_SESSION['CompanyRecord']['regoffice1']?> </b></td>
 		<td colspan='2' nowrap width="50%"> <b><?=$myrow['deliverto']?></td>
 	</tr>
  	<tr>
  		<td colspan='2' nowrap width="50%"> 
  			<b><?=$_SESSION['CompanyRecord']['regoffice4']?>,
				<?=$_SESSION['CompanyRecord']['regoffice5']?> </b>
		</td>
		<td colspan='2' nowrap width="50%"> <b><?=$myrow['deladd1']?></td>
	</tr>
 	<tr>
 		<td colspan='2' nowrap width="50%"> 
 			<b><?=$_SESSION['CompanyRecord']['telephone']?>  
 			Fax:<?=$_SESSION['CompanyRecord']['fax']?></b>
 		</td>
 		<td nowrap width="50%"><b><?=$myrow['deladd2']?></td>
 	</tr>
 	<tr>
 		<td colspan='2' nowrap width="50%"> 
 			<b><?=$_SESSION['CompanyRecord']['email']?><br><br><br>
 		</td> 		
     		<td nowrap width="50%">
       		<b><?=$myrow['deladd3']?> <?=$myrow['deladd4'] ?> <?=$myrow['deladd5']?><br><br><br>
      		</td>
 	</tr>
</table>
<table border='1' width='60%' cellpadding="2" cellspacing='2'>
	<tr>
<?
if($_REQUEST['POLine'] == 1){
?>	
		<td align="center">PO Line</td>
<?
}
?>		
		<td align="center">Stock Code</td>
		<td align="center">Description</td>
		<td align="center">Quantity Ordered</td>
      		<td align="center">Due Date</td>
   	</tr>
<?
For( $j=0; $j<$i; $j++)
{
?>
	<tr>
<?
	if($_REQUEST['POLine']){
?>	
		<td align='right'><?=$POLine[$j]?></td>
<?
	}
?>		
		<td><?=$StkCode[$j]?></td>
		<td><?=$DscCode[$j]?></td>
		<td align="right"><?=$QtyCode[$j]?></td>
      		<td align="center"><?=$ItemDue[$j]?></td>
   	</tr>
<?
}
?>   
</table>
</body>
</html>
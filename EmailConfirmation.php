<?php


include('includes/session.php');
include('includes/SQL_CommonFunctions.php');

$ViewTopic = 'SalesOrders';
$BookMark = '';
//Get Out if we have no order number to work with
If (!isset($_GET['TransNo']) OR $_GET['TransNo']==''){
	$Title = _('Select Order To Print');
	include('includes/header.php');
	echo '<div class="centre">
			<br />
			<br />
			<br />';
	prnMsg( _('Select an Order Number to Print before calling this page') , 'error');
	echo '<br />
			<br />
			<br />
			<table class="table_index">
			<tr>
				<td class="menu_group_item">
					<ul>
						<li><a href="'. $RootPath . '/SelectSalesOrder.php">' . _('Outstanding Sales Orders') . '</a></li>
						<li><a href="'. $RootPath . '/SelectCompletedOrder.php">' . _('Completed Sales Orders') . '</a></li>
					</ul>
				</td>
			</tr>
			</table>
			</div>
			<br />
			<br />
			<br />';
	include('includes/footer.php');
	exit;
}

$MailTo = $_GET['EMail'];
$Headers = 'From: weberp.org <info@weberp.org>' . '\n';
$Headers  .=  'MIME-Version: 1.0\n' . 'Content-Type: text/html; charset="utf-8"\n';

/*retrieve the order details from the database to print */
$ErrMsg = _('There was a problem retrieving the order header details for Order Number') . ' ' . $_GET['TransNo'] . ' ' . _('from the database');

$SQL = "SELECT salesorders.debtorno,
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
			FROM salesorders
			INNER JOIN locationusers ON locationusers.loccode=salesorders.fromstkloc AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1,
				debtorsmaster,
				shippers,
				locations
			WHERE salesorders.debtorno=debtorsmaster.debtorno
			AND salesorders.shipvia=shippers.shipper_id
			AND salesorders.fromstkloc=locations.loccode
			AND salesorders.orderno='" . $_GET['TransNo'] . "'";

$Result=DB_query($SQL, $ErrMsg);

//If there are no rows, there's a problem.
if (DB_num_rows($Result)==0){
	$Title = _('Print Packing Slip Error');
	include('includes/header.php');
	 echo '<div class="centre">
			<br />
			<br />
			<br />';
	prnMsg( _('Unable to Locate Order Number') . ' : ' . $_GET['TransNo'] . ' ', 'error');
	echo '<br />
			<br />
			<br />
			<table class="table_index">
			<tr>
				<td class="menu_group_item">
				<ul>
	                <li><a href="'. $RootPath . '/SelectSalesOrder.php">' . _('Outstanding Sales Orders') . '</a></li>
	                <li><a href="'. $RootPath . '/SelectCompletedOrder.php">' . _('Completed Sales Orders') . '</a></li>
				</ul>
				</td>
			</tr>
			</table>
			</div>
			<br />
			<br />
			<br />';
	include('includes/footer.php');
	exit;
} elseif (DB_num_rows($Result)==1){ /*There is only one order header returned - thats good! */

	$MyRow = DB_fetch_array($Result);
	/* Place the deliver blind variable into a hold variable to used when
	producing the packlist */
	$DeliverBlind = $MyRow['deliverblind'];
	$DeliveryDate = $MyRow['salesorders.deliverydate'];
	if ($MyRow['printedpackingslip']==1 AND ($_GET['Reprint']!='OK' OR !isset($_GET['Reprint']))){
		$Title = _('Print Packing Slip Error');
		include('includes/header.php');
		prnMsg( _('The packing slip for order number') . ' ' . $_GET['TransNo'] . ' ' . _('has previously been printed') . ' ' . _('It was printed on'). ' ' . ConvertSQLDate($MyRow['datepackingslipprinted']) . '<br />' . _('This check is there to ensure that duplicate packing slips are not produced and dispatched more than once to the customer'), 'warn' );
		echo '<p><a href="' . $RootPath . '/PrintCustOrder.php?TransNo=' . $_GET['TransNo'] . '&Reprint=OK">'
		. _('Do a Re-Print') . ' (' . _('On Pre-Printed Stationery') . ') ' . _('Even Though Previously Printed') . '</a></p><p><a href="' . $RootPath. '/PrintCustOrder_generic.php?TransNo=' . $_GET['TransNo'] . '&Reprint=OK">' .  _('Do a Re-Print') . ' (' . _('Plain paper') . ' - ' . _('A4') . ' ' . _('landscape') . ') ' . _('Even Though Previously Printed'). '</a></p>';

		echo '<br />
				<br />
				<br />';
		echo  _('Or select another Order Number to Print');
		echo '<table class="table_index">
				<tr>
					<td class="menu_group_item">
					<ul>
						<li><a href="'. $RootPath . '/SelectSalesOrder.php">' . _('Outstanding Sales Orders') . '</a></li>
						<li><a href="'. $RootPath . '/SelectCompletedOrder.php">' . _('Completed Sales Orders') . '</a></li>
					</ul>
					</td>
				</tr>
			</table>
			</div>
			<br />
			<br />
			<br />';

		include('includes/footer.php');
		exit;
	}//packing slip has been printed.
	$MailSubject = _('Order Confirmation-Sales Order') . ' ' .  $_GET['TransNo'] . ' - '. _('Your PO') . ' ' . $MyRow['customerref'] ;
}

$MailMessage =  '<html>
				<head>
					<title>' . _('Email Confirmation') . '</title>
				</head>
				<body>
				<table cellpadding="2" cellspacing="2">
				<tr>
					<td align="center" colspan="4"><h1>' . $_SESSION['CompanyRecord']['coyname'] . '</h1></td>
				</tr>
				<tr>
					<td colspan="4"> <b>' . $_SESSION['CompanyRecord']['regoffice1'] . '</td>
				</tr>
				<tr>
					<td colspan="4"> <b>' . $_SESSION['CompanyRecord']['regoffice4'] . ',<b>' . $_SESSION['CompanyRecord']['regoffice5'] . '</td>
				</tr>
				<tr>
					<td colspan="4"> <b>' . $_SESSION['CompanyRecord']['telephone'] . ' ' . _('Fax'). ': ' . $_SESSION['CompanyRecord']['fax'] . '</td>
				</tr>
				<tr>
					<td colspan="4"> <b>' . $_SESSION['CompanyRecord']['email'] . '
					<br />
					<br />
					<br /></td>
				</tr>
				</table>
				<table>
					<tr>
						<td align="center" colspan="4">
							<h2>' . _('Order Acknowledgement') . '</h2>
						</td>
				</tr>
				<tr>
					<td align="center" colspan="4"> <b>' . _('Order Number') . ' ' . $_GET['TransNo'] . '</b>
					<br />
					<br />
					<br /></td>
				</tr>
				<tr>
					<td colspan="4"> <b>' . _('Delivered To') . ':</b></td>
				</tr>
				<tr>
					<td colspan="4"> <b>' . $MyRow['deliverto'] . '</td>
				</tr>
				<tr>
					<td colspan="4"> <b>' . $MyRow['deladd1'] . '</td>
				</tr>';

if(mb_strlen(trim($MyRow['deladd2']))) {
      $MailMessage .= '<tr>
						<td> <b>' . $MyRow['deladd2'] . '</td>
					</tr>
					<tr>
						<td> <b>' . $MyRow['deladd3'] . ' ' . $MyRow['deladd4'] . ' ' . $MyRow['deladd5']. '
							<br />
							<br />
							<br /></td>
					/tr>';
} else {
      $MailMessage .= '<tr>
						<td> <b>' . $MyRow['deladd3'] . ' ' . $MyRow['deladd4'] . ' ' . $MyRow['deladd5'] . '
							<br />
							<br />
							<br /></td>
					</tr>';
}
$MailMessage .= '</table>
				<table border="1" width="50%"><tr>';
if($_GET['POLine'] == 1){
	$MailMessage .= '<td>' . _('PO Line') . '</td>';
}
	$MailMessage .= '<td>' . _('Stock Code') . '</td>
					<td>' . _('Description') . '</td>
					<td>' . _('Quantity Ordered') . '</td>
					<td>' . _('Due Date') . '</td>
					</tr>';


	$SQL = "SELECT salesorderdetails.stkcode,
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
	$Result=DB_query($SQL, $ErrMsg);
	$i=0;
	if (DB_num_rows($Result)>0){

		while ($MyRow2=DB_fetch_array($Result)){

			$DisplayQty = locale_number_format($MyRow2['quantity'],0);
			$DisplayPrevDel = locale_number_format($MyRow2['qtyinvoiced'],0);
			$DisplayQtySupplied = locale_number_format($MyRow2['quantity'] - $MyRow2['qtyinvoiced'],0);
         		$StkCode[$i] = $MyRow2['stkcode'];
         		$DscCode[$i] = $MyRow2['description'];
         		$QtyCode[$i] = $DisplayQty ;
         		$POLine[$i]  = $MyRow2['poline'];
        		if($MyRow2['itemdue'] =='') {
         			$ItemDue[$i] = date('M d, Y',strtotime($DeliveryDate));
        		} else {
        			$ItemDue[$i] = date('M d, Y',strtotime($MyRow2['itemdue']));
        		}
			$MailMessage .= '<tr>';
			if($_GET['POLine'] == 1){
				$MailMessage .= '<td align="right">' . $POLine[$i] . '</td>';
			}
			$MailMessage .= '<td>' . $MyRow2['stkcode'] . '</td>
							<td>' . $MyRow2['description'] . '</td>
							<td align="right">' . $DisplayQty . '</td>
							<td align="center">' . $ItemDue[$i]  . '</td>
							</tr>';
			$i++;
		} //end while there are line items to print out
	} /*end if there are order details to show on the order*/
	$MailMessage .= '</table>
				</body>
				</html>';

	SendEmailFromWebERP($SysAdminEmail, 
						$MailTo,
						$MailSubject,
						$MailMessage,
						'',
						false);
if($Result){
	echo ' ' ._('The following E-Mail was sent to') . ' ' . $MailTo . ' :';
}


echo '<html>
	<head>
	<title>' . _('Email Confirmation') . '</title>
	</head>
	<body>
	<table width="60%">
		<tr>
			<td align="center" colspan="4"><img src="' . $RootPath . '/' . $_SESSION['LogoFile'] . '" alt="Logo" width="500" height="100" align="center" border="0" /></td>
	   	</tr>
		<tr>
			<td align="center" colspan="4"><h2>' . _('Order Acknowledgement') . '</h2></td>
		</tr>
	 	<tr>
	 		<td align="center" colspan="4"> <b>' . _('Order Number') .  ' ' . $_GET['TransNo'] . '</b>
			<br />
			<br />
			<br /></td>
	 	</tr>
	 	<tr>
	 		<td colspan="2" nowrap width="50%"> <b>' . $_SESSION['CompanyRecord']['coyname'] . '</b></td>
	 		<td colspan="2" nowrap width="50%"> <b>' . _('Delivered To') . ':</b></td>
	 	</tr>
	 	<tr>
	 		<td colspan="2" nowrap width="50%"> <b>' . $_SESSION['CompanyRecord']['regoffice1'] . '</b></td>
	 		<td colspan="2" nowrap width="50%"> <b>' . $MyRow['deliverto'] . '</td>
	 	</tr>
	  	<tr>
	  		<td colspan="2" nowrap width="50%">
	  			<b>' . $_SESSION['CompanyRecord']['regoffice4'] . ',
				<br />' . $_SESSION['CompanyRecord']['regoffice5'] . '</b>
			</td>
			<td colspan="2" nowrap width="50%"> <b>' . $MyRow['deladd1'] . '</td>
		</tr>
	 	<tr>
	 		<td colspan="2" nowrap width="50%">
	 			<b>' . $_SESSION['CompanyRecord']['telephone'] . '
	 			<br />' . _('Fax') . ': ' . $_SESSION['CompanyRecord']['fax'] . '</b>
	 		</td>
	 		<td nowrap width="50%"><b>' . $MyRow['deladd2'] . '</td>
	 	</tr>
	 	<tr>
	 		<td colspan="2" nowrap width="50%">
	 			<b>' . $_SESSION['CompanyRecord']['email'] . '
	 			<br />
	 			<br />
	 			<br />
	 		</td>
	     		<td nowrap width="50%">
	       		<b>' . $MyRow['deladd3'] . ' ' . $MyRow['deladd4'] . ' ' . $MyRow['deladd5'] . '
	       		<br />
	       		<br />
	       		<br />
	      		</td>
	 	</tr>
	</table>
	<table border="1" width="60%" cellpadding="2" cellspacing="2">
	<tr>';

if($_GET['POLine'] == 1){
	echo '<td align="center">' . _('PO Line') . '</td>';
}
echo '<td align="center">' . _('Stock Code') . '</td>
	<td align="center">' . _('Description') . '</td>
	<td align="center">' . _('Quantity Ordered') . '</td>
	<td align="center">' . _('Due Date') . '</td>
   	</tr>';

for( $j=0; $j<$i; $j++){
	echo '<tr>';
	if($_GET['POLine']){
		echo '<td align="right">' . $POLine[$j] . '</td>';
	}
	echo '<td>' . $StkCode[$j] . '</td>
			<td>' . $DscCode[$j] . '</td>
			<td align="right">' . $QtyCode[$j] . '</td>
			<td align="center">' . $ItemDue[$j] . '</td>
		</tr>';
}
echo '</table>
	</body>
	</html>';
?>

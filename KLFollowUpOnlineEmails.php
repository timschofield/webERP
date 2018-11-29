<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
$Title = _('Kapal-Laut Follow Up Email System');
include('includes/header.php');

//Get Out if we have no order number to work with
If (!isset($_GET['TransNo']) OR $_GET['TransNo']==''){
	prnMsg( _('We need an order number to send an email to online customer') , 'error');
	include('includes/footer.php');
	exit;
}
If (!isset($_GET['EmailType']) OR $_GET['EmailType']==''){
	prnMsg( _('We need an email type to send an email to online customer') , 'error');
	include('includes/footer.php');
	exit;
}

if ($_GET['EmailType']!='NoSendThankYou'){
	// if we send an email different than "no send thank you email", we have to prepare the mail text
	$headers = "From: " . $_SESSION['ShopName'] . " <" . strip_tags($_SESSION['ShopManagerEmail']) . ">\r\n";
	$headers .= "Reply-To: " . $_SESSION['ShopName'] . " <". strip_tags($_SESSION['ShopManagerEmail']) . ">\r\n";
	$headers .= "Cc: " . $_SESSION['ShopName'] . " <". strip_tags($_SESSION['ShopManagerEmail']) . ">\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=utf-8\r\n";

	if ($_GET['EmailType']=='NoOrderPlaced'){
		$MailSubject = $_SESSION['ShopName'] . ' ' . _('Customer Registration');
		$sql = "SELECT debtorsmaster.debtorno,
						debtorsmaster.name AS customername,
						debtorsmaster.clientsince,
						debtorsmaster.address1,
						debtorsmaster.address2,
						debtorsmaster.address3,
						debtorsmaster.address4,
						debtorsmaster.address5,
						debtorsmaster.address6,
						debtorsmaster.currcode,
						custbranch.email AS contactemail
				FROM debtorsmaster, custbranch
				WHERE debtorsmaster.debtorno = custbranch.debtorno
					AND debtorsmaster.debtorno='" . $_GET['TransNo'] . "'";
					
	}elseif ($_GET['EmailType']=='RemindBankTransfer'){
		$MailSubject = $_SESSION['ShopName'] . ' ' . _('Bank Transfer Confirmation needed for Order') . ': ' . locale_number_format($_GET['CustomerOrder'],0) . ' (' . locale_number_format($_GET['TransNo'],0) . ')';
		$sql = "SELECT salesorders.debtorno,
						salesorders.customerref,
						salesorders.comments,
						salesorders.orddate,
						salesorders.deliverto AS customername,
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
						salesorders.freightcost,
						shippers.shippername,
						salesorders.printedpackingslip,
						salesorders.datepackingslipprinted,
						locations.locationname,
						salesorders.deliverydate,
						salesorders.contactemail,
						currencies.decimalplaces,
						debtorsmaster.currcode
				FROM salesorders,
					debtorsmaster,
					shippers,
					locations,
					currencies
				WHERE salesorders.debtorno=debtorsmaster.debtorno
					AND salesorders.shipvia=shippers.shipper_id
					AND salesorders.fromstkloc=locations.loccode
					AND currencies.currabrev=debtorsmaster.currcode 
					AND salesorders.orderno='" . $_GET['TransNo'] . "'";
					
	}elseif ($_GET['EmailType']=='PaymentConfirmation'){
		$MailSubject = $_SESSION['ShopName'] . ' ' . _('Payment Confirmation for Order') . ': ' . locale_number_format($_GET['CustomerOrder'],0) . ' (' . locale_number_format($_GET['TransNo'],0) . ')';
		$sql = "SELECT salesorders.debtorno,
					salesorders.customerref,
					salesorders.comments,
					salesorders.orddate,
					salesorders.deliverto AS customername,
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
					salesorders.freightcost,
					shippers.shippername,
					salesorders.printedpackingslip,
					salesorders.datepackingslipprinted,
					locations.locationname,
					salesorders.deliverydate,
					salesorders.contactemail,
					currencies.decimalplaces,
					debtorsmaster.currcode,
					debtortrans.consignment
			FROM salesorders,
					debtorsmaster,
					shippers,
					locations,
					currencies,
					debtortrans
			WHERE salesorders.debtorno=debtorsmaster.debtorno
				AND salesorders.shipvia=shippers.shipper_id
				AND salesorders.fromstkloc=locations.loccode
				AND currencies.currabrev=debtorsmaster.currcode 
				AND debtortrans.order_=salesorders.orderno
				AND (debtortrans.type = 12 AND salesorders.klemailtrackingconfirm = '0000-00-00') 
				AND salesorders.orderno='" . $_GET['TransNo'] . "'";
		
	}elseif ($_GET['EmailType']=='TrackingConfirmation'){
		$MailSubject = $_SESSION['ShopName'] . ' ' . _('Shipment Confirmation for Order') . ': ' . locale_number_format($_GET['CustomerOrder'],0) . ' (' . locale_number_format($_GET['TransNo'],0) . ')';
		$sql = "SELECT salesorders.debtorno,
						salesorders.customerref,
						salesorders.comments,
						salesorders.orddate,
						salesorders.deliverto AS customername,
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
						salesorders.freightcost,
						shippers.shippername,
						salesorders.printedpackingslip,
						salesorders.datepackingslipprinted,
						locations.locationname,
						salesorders.deliverydate,
						salesorders.contactemail,
						currencies.decimalplaces,
						debtorsmaster.currcode, 
						debtortrans.consignment
				FROM salesorders,
						debtorsmaster,
						shippers,
						locations,
						currencies,
						debtortrans
				WHERE salesorders.debtorno=debtorsmaster.debtorno
					AND salesorders.shipvia=shippers.shipper_id
					AND salesorders.fromstkloc=locations.loccode
					AND currencies.currabrev=debtorsmaster.currcode 
					AND debtortrans.order_=salesorders.orderno
					AND (debtortrans.type = 10) 
					AND salesorders.orderno='" . $_GET['TransNo'] . "'";
	}elseif ($_GET['EmailType']=='ThankYouOrder'){
		$MailSubject = $_SESSION['ShopName'] . ' ' . _(' Thank You!') ;
		$sql = "SELECT salesorders.debtorno,
						salesorders.customerref,
						salesorders.comments,
						salesorders.orddate,
						salesorders.deliverto AS customername,
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
						salesorders.freightcost,
						shippers.shippername,
						salesorders.printedpackingslip,
						salesorders.datepackingslipprinted,
						locations.locationname,
						salesorders.deliverydate,
						salesorders.contactemail,
						currencies.decimalplaces,
						debtorsmaster.currcode, 
						debtortrans.consignment
				FROM salesorders,
						debtorsmaster,
						shippers,
						locations,
						currencies,
						debtortrans
				WHERE salesorders.debtorno=debtorsmaster.debtorno
					AND salesorders.shipvia=shippers.shipper_id
					AND salesorders.fromstkloc=locations.loccode
					AND currencies.currabrev=debtorsmaster.currcode 
					AND debtortrans.order_=salesorders.orderno
					AND (debtortrans.type = 10) 
					AND salesorders.orderno='" . $_GET['TransNo'] . "'";
	}


	$ErrMsg = _('There was a problem retrieving the order header details for Order Number') . ' ' . $_GET['TransNo'] . ' ' . _('from the database');
	$result=DB_query($sql, $ErrMsg);

	//If there are no rows, there's a problem.
	if (DB_num_rows($result)==0){
		prnMsg( _('Unable to Locate Order Number') . ' : ' . $_GET['TransNo'] . ' ', 'error');
		include('includes/footer.php');
		exit;
	} elseif (DB_num_rows($result)==1){ /*There is only one order header returned - thats good! */
		$myrow = DB_fetch_array($result);
		$DeliverBlind = $myrow['deliverblind'];
		$DeliveryDate = $myrow['salesorders.deliverydate'];
	}

	/* Introduction text */
	$MailMessage = '
		<html>
		<head>
			<title>' .$MailSubject . '</title>
		</head>
		<body>
			<br/>
			<table cellpadding="2" cellspacing="2">
				<tr>
					<td align="center" colspan="4">
						<h2>' . $MailSubject . '</h2>
					</td>
				</tr>
				<tr>
					<td colspan="4">
						<p>' . 'Hi ' . DB_escape_string($myrow['customername']) . ':</p>
					</td>
				</tr>';
				
	if ($_GET['EmailType']=='NoOrderPlaced'){
		  $MailMessage .= '
				<tr>
					<td colspan="4">
						<p>' . 'On ' . ConvertSQLDate($myrow['clientsince']) . ' you registered in our online shop, but we realized you did not continue the purchase process.' . '</p>
					</td>
				</tr>
				</table>';
		$MailMessage .= '	
				<table>
					<tr>
						<th> ' . 'You were registered from this email address as' . ':
						</th>
						<td>' . DB_escape_string($myrow['customername']) . '
						</td>
					</tr>';
		if(mb_strlen(trim($myrow['address1']))) {
			  $MailMessage .= '
					<tr>
						<td>
						</td>
						<td>' . DB_escape_string($myrow['address1']) . '
						</td>
					</tr>';
		}
		if(mb_strlen(trim($myrow['address2']))) {
			  $MailMessage .= '
					<tr>
						<td>
						</td>
						<td>' . DB_escape_string($myrow['address2']) . '
						</td>
					</tr>';
		}
		if(mb_strlen(trim($myrow['address3']))) {
			  $MailMessage .= '
					<tr>
						<td>
						</td>
						<td>' . DB_escape_string($myrow['address3']) . '
						</td>
					</tr>';
		}
		if(mb_strlen(trim($myrow['address4']))) {
			  $MailMessage .= '
					<tr>
						<td>
						</td>
						<td>' . DB_escape_string($myrow['address4']) . '
						</td>
					</tr>';
		}
		if(mb_strlen(trim($myrow['address5']))) {
			  $MailMessage .= '
					<tr>
						<td>
						</td>
						<td>' . DB_escape_string($myrow['address5']) . '
						</td>
					</tr>';
		}
		if(mb_strlen(trim($myrow['address6']))) {
			  $MailMessage .= '
					<tr>
						<td>
						</td>
						<td>' . DB_escape_string($myrow['address6']) . '
						</td>
					</tr>';
		}

		$MailMessage .= '
				</table>';

		$MailMessage .= '<br/>';
		$MailMessage .= '
				<table cellpadding="2" cellspacing="2">
				<tr>
					<td colspan="4">
						<ul>
							<li>' . 'If you found any shop malfunction, purchase issue or did not find the product you were looking for, please let us know so we can fix it or help you.' . '</li>
							<li>' . 'If you changed your mind during the process, we would like also know why, so we can improve the customer experience on our online shop. We want to treat the online visitor with the same care and detail we do on our street shops. Please help us to do it!' . '</li>
							<li>' . 'If you did not registered yourself on our online shop, someone else did it using your data. Please let us know so we can delete your data from our system.' . '</li>
						</ul>
					</td>
				</tr>';
		$MailMessage .= '<br/>';
	}

	if ($_GET['EmailType']=='RemindBankTransfer'){
		  $MailMessage .= '
				<tr>
					<td colspan="4">
						<p>' . 'On ' . ConvertSQLDate($myrow['orddate']) . ' we received an online order from you to be settle by bank transfer to our account, but after checking our bank account we have not received funds from you.' . '</p>
					</td>
				</tr>
				</table>';
		  $MailMessage .=  ShowBankDetails ($myrow['currcode'], $_GET['TransNo']);
		  $MailMessage .= '
				<table cellpadding="2" cellspacing="2">
				<tr>
					<td colspan="4">
						' . 'If you already send the funds or plan to send send the funds via bank transfer: Please check with your bank and send us the scanned proof of transfer, so we can double check in our bank.' . '
					</td>
				</tr>';
		$MailMessage .= '<br/>';
	}

	if ($_GET['EmailType']=='PaymentConfirmation'){
		  $MailMessage .= '
				<tr>
					<td colspan="4">
						<p>' . 'We just received the confirmation of your payment for the order ' . locale_number_format($_GET['CustomerOrder'],0) . '</p>
					</td>
				</tr>';
	}

	if ($_GET['EmailType']=='TrackingConfirmation'){
		if($myrow['shippername'] == 'Pick up from store') {
			$MailMessage .= '
				<tr>
					<td colspan="4">
						' . 'Your parcel is ready to pick up from the chosen store. In case we agreed a delivery in Bali, it will be delivered there soon. Please contact us for further details if needed.' . '
					</td>
				</tr>';
		}else{
			$MailMessage .= '
				<tr>
					<td colspan="4">
						<p>' . 'We just shipped your order via ' . DB_escape_string($myrow['shippername']) . '. The tracking number of your parcel is:' . DB_escape_string($myrow['consignment']) .'</p>
					</td>
				</tr>';
			if($myrow['shippername'] == 'EMS'){
				$MailMessage .= '
					<tr>
						<td colspan="4">
							' . 'You can track your shipment at http://ems.posindonesia.co.id/index.php' . '
						</td>
					</tr>';
			}else{
				$MailMessage .= '
					<tr>
						<td colspan="4">
							' . 'You can track your shipment at http://jne.co.id/index.php?lang=IN' . '
						</td>
					</tr>';
			 }
			$MailMessage .= '
				<tr>
					<td colspan="4">
						' . 'For delivery transit schedules and general information check http://www.kapal-laut.com/Delivery-Shipping-Information' . '
					</td>
				</tr>';
		}
		$MailMessage .= '
			<tr>
				<td colspan="4">
					<p>' . 'Please let us know when you receive it, and we would like to see some pictures of you wearing the jewels! You can post them on our Facebook Fan Page at www.facebook.com/KapalLautBali ' . '</p>
				</td>
			</tr>';
	}

	if ($_GET['EmailType']=='ThankYouOrder'){
		 $MailMessage .= '
				<tr>
					<td colspan="4">
						<p>' . 'On '. ConvertSQLDate($myrow['orddate']) .' you purchased some jewellery from us, and we shipped it via ' . DB_escape_string($myrow['shippername']). '.</p>
						<p>' . 'You should have received it few days ago, and we very much hope you are enjoying your new jewels. ' . '</p>
						<p>' . 'Should you wish to comment your experience on our Facebook fan page you can find us here http://www.facebook.com/KapalLautBali.' . '</p>
						<p>' . 'Be sure we will read it to all our staff at our daily briefing! We do take all feedback seriously. If there is any information that you think will help us improve, feel free to email us direct to sales@kapal-laut.com ' . '</p>
					</td>
				</tr>';
	}
	$MailMessage .= '			
			</table>';

	if (($_GET['EmailType']=='PaymentConfirmation') 
		OR	($_GET['EmailType']=='TrackingConfirmation')
		OR	($_GET['EmailType']=='TrackingConfirmation')){
		/* Email the order details */
		$MailMessage .= '	
				<table>
					<tr>
						<th> <b>' . _('Order to be delivered to') . ':</b>
						</th>
						<td>' . DB_escape_string($myrow['customername']) . '
						</td>
					</tr>';
		if(mb_strlen(trim($myrow['deladd1']))) {
			  $MailMessage .= '
					<tr>
						<td>
						</td>
						<td>' . DB_escape_string($myrow['deladd1']) . '
						</td>
					</tr>';
		}
		if(mb_strlen(trim($myrow['deladd2']))) {
			  $MailMessage .= '
					<tr>
						<td>
						</td>
						<td>' . DB_escape_string($myrow['deladd2']) . '
						</td>
					</tr>';
		}
		if(mb_strlen(trim($myrow['deladd3']))) {
			  $MailMessage .= '
					<tr>
						<td>
						</td>
						<td>' . DB_escape_string($myrow['deladd3']) . '
						</td>
					</tr>';
		}
		if(mb_strlen(trim($myrow['deladd4']))) {
			  $MailMessage .= '
					<tr>
						<td>
						</td>
						<td>' . DB_escape_string($myrow['deladd4']) . '
						</td>
					</tr>';
		}
		if(mb_strlen(trim($myrow['deladd5']))) {
			  $MailMessage .= '
					<tr>
						<td>
						</td>
						<td>' . DB_escape_string($myrow['deladd5']) . '
						</td>
					</tr>';
		}
		if(mb_strlen(trim($myrow['deladd6']))) {
			  $MailMessage .= '
					<tr>
						<td>
						</td>
						<td>' . DB_escape_string($myrow['deladd6']) . '
						</td>
					</tr>';
		}

		$MailMessage .= '
				</table>';

		$MailMessage .= '<br/>';

		/* order items details */

		$MailMessage .= '
				<table border="1" width="90%">
					<tr>
						<th>' . _('Stock Code') . '</th>
						<th>' . _('Description') . '</th>
						<th>' . _('Quantity') . '</th>
						<th>' . _('Unit Price') . '</th>
						<th>' . _('Line Price') . '</th>
					</tr>';
			
		$sql = "SELECT salesorderdetails.stkcode,
				stockmaster.description,
				stockmaster.grossweight,
				stockmaster.volume,
				salesorderdetails.quantity,
				salesorderdetails.qtyinvoiced,
				salesorderdetails.unitprice,
				salesorderdetails.discountpercent,
				salesorderdetails.narrative,
				salesorderdetails.poline,
				salesorderdetails.itemdue
			FROM salesorderdetails INNER JOIN stockmaster
				ON salesorderdetails.stkcode=stockmaster.stockid
			WHERE salesorderdetails.orderno=" . $_GET['TransNo'] . "
			ORDER BY poline";
		$result=DB_query($sql, $ErrMsg);
		$CartTotalValue  = 0;
		$CartTotalWeight = 0;
		$CartTotalVolume = 0;
		if (DB_num_rows($result)>0){
			while ($myrow2=DB_fetch_array($result)){
	//			14/01/2015 
	// 			ONLINE ORDERS already come with NET prices
				$GrossPrice = round($myrow2['unitprice'],$myrow['decimalplaces']) ;
				$LineTotal = $GrossPrice * $myrow2['quantity'];
				$CartTotalValue += $LineTotal;
				$CartTotalWeight += $CartItem->Weight * $CartItem->Quantity;
				$CartTotalVolume += $CartItem->Volume * $CartItem->Quantity;
				$MailMessage .= '
						<tr>
							<td>' . $myrow2['stkcode'] . '</td>
							<td>' . $myrow2['description'] . '</td>
							<td align="right">' . locale_number_format($myrow2['quantity'],0) . '</td>
							<td align="right">' .  locale_number_format($GrossPrice,$myrow['decimalplaces'])  . '</td>
							<td align="right">' .  locale_number_format($LineTotal,$myrow['decimalplaces'])  . '</td>
						</tr>';		
			} //end while there are line items to print out
		} /*end if there are order details to show on the order*/

		$MailMessage .= '
					<tr>
						<td colspan="4" align="right">' . _('Total Items Ordered Value') . '</td>
						<td align="right">' . locale_number_format($CartTotalValue,$myrow['decimalplaces']) . '</td>
					</tr>';

		/* freight details */
		if ($myrow['freightcost'] != 0){
			$MailMessage .=  '
				<tr>
					<td colspan="4" align="right">' . _('Freight Costs') . '</td>
					<td align="right">' . locale_number_format($myrow['freightcost'],$myrow['decimalplaces']) . '</td>
				</tr>';
		}else{
			if ($myrow['shippername'] == 'Pick up from store'){
				$MailMessage .=  '
					<tr>
						<td colspan="4" align="right">' . _('Pick up from store') . '</td>
					</tr>';
			}else{
				$MailMessage .=  '
					<tr>
						<td colspan="4" align="right">' . _('Freight Costs paid by') . ' ' . $_SESSION['ShopName'] . '</td>
					</tr>';
			}
		}
			$MailMessage .=  '
				<tr>
					<td colspan="4" align="right">' . _('Total') . ' (' . $myrow['currcode'] . ') ' . '</td>
					<td align="right">' . locale_number_format($CartTotalValue + $myrow['freightcost'],$myrow['decimalplaces']) . '</td>
				</tr>';
		$MailMessage .= '
				</table>';
	}
	$MailMessage .= '<br/>';
	/* Despedida */
	$MailMessage .= '<table cellpadding="2" cellspacing="2">';

	if ($_GET['EmailType']=='PaymentConfirmation'){
		$MailMessage .= '<tr>
					<td colspan="4">
						<p>' . 'We will prepare all the goods and ship it in 2-3 days time. We will email you the shipment tracking number.' . '</p>
						<p>' . 'Do not hesitate to contact us for any further detail you might need. Many thanks for your purchase.' . '</p>
					</td>
				</tr>';
	}

	if ($_GET['EmailType']=='ThankYouOrder'){
		$MailMessage .= '<tr>
					<td colspan="4">
						<p>' . 'Again, thank you for your purchase. We hope you enjoy your new jewels.' . '</p>
					</td>
				</tr>';
	}

	if ($_GET['EmailType']=='TrackingConfirmation'){
		$MailMessage .= '<tr>
					<td align="center" colspan="4">
						<p>' . 'Do not hesitate to contact us for any further detail you might need. Many thanks for your purchase.' . '</p>
					</td>
				</tr>';
	}
	if (($_GET['EmailType']=='NoOrderPlaced')
		OR	($_GET['EmailType']=='RemindBankTransfer')){
		$MailMessage .= '<tr>
					<td align="center" colspan="4">
						<p>' . 'Do not hesitate to contact us for any further detail you might need. Many thanks.' . '</p>
					</td>
				</tr>';
	}
	$MailMessage .= '<tr>
					<td colspan="4">
						<p>' .  _('Kapal-Laut Your Essential Jewellery Online Sales Team.') . '</p>
					</td>
				</tr>';

	$MailMessage .= '</table>';
	$MailMessage .= '</body>
					</html>';
}
if($_SESSION['ShopMode']=='test'){
	// do not bother customers if we are doing tests with their data
	$MailTo = $_SESSION['ShopManagerEmail'];
}else{
	$MailTo = $myrow['contactemail'];
}


if($_SESSION['SmtpSetting']==0){
	$result = mail( $MailTo, $MailSubject, $MailMessage, $headers );
}else{
	include('includes/htmlMimeMail.php');
	$mail = new htmlMimeMail();
	$mail->setSubject($mailSubject);
	$mail->setHTML($MailMessage);
	$result = SendmailBySmtp($mail,array($MailTo));
}

if ($_GET['EmailType']!='NoSendThankYou'){
	echo '<h1>Email sent to ' . $MailTo. '</h1><br />';
	echo $MailMessage . "<br />";
}else{
	echo '<h1>NO Thank you Email for order ' . $_GET['TransNo'] . '</h1><br />';
}

if ($_GET['EmailType']=='NoOrderPlaced'){
	$sql = "UPDATE debtorsmaster 
			SET klemailnowebshoporder = '" . Date('Y-m-d') . "'
			WHERE debtorno =	'" . $_GET['TransNo'] . "'";
	$ErrMsg =_('Could not update the customers KL email no order placed date because');
	$result = DB_query($sql,$ErrMsg);
	prnMsg("Updated date of sending remind of no Online Shop order placed to today");
}

if ($_GET['EmailType']=='RemindBankTransfer'){
	$sql = "UPDATE salesorders 
			SET klemailremindbanktransfer = '" . Date('Y-m-d') . "'
			WHERE orderno =	'" . $_GET['TransNo'] . "'";
	$ErrMsg =_('Could not update the sales order KL email remind bank transfer date because');
	$result = DB_query($sql,$ErrMsg);
	prnMsg("Updated date of sending remind bank transfer to online customer to today");
}

if ($_GET['EmailType']=='PaymentConfirmation'){
	if($_SESSION['ShopMode']!='test'){
		// send a confirmation to team, so they prepare a new order ASAP, if it is NOT a test
		$MailTo = "kl-onlinesupport@kapal-laut.com";
		$MailSubject = "New order online. Process ASAP.";
		if($_SESSION['SmtpSetting']==0){
			$result = mail( $MailTo, $MailSubject, $MailMessage, $headers );
		}else{
			include('includes/htmlMimeMail.php');
			$mail = new htmlMimeMail();
			$mail->setSubject($mailSubject);
			$mail->setHTML($MailMessage);
			$result = SendmailBySmtp($mail,array($MailTo));
		}
	}
	// update the sales order, as we send the payment confirmation
	$sql = "UPDATE salesorders 
			SET klemailpaymentconfirm = '" . Date('Y-m-d') . "'
			WHERE orderno =	'" . $_GET['TransNo'] . "'";
	$ErrMsg =_('Could not update the sales order KL email payment confirmation date because');
	$result = DB_query($sql,$ErrMsg);
	prnMsg("Updated date of sending remind bank transfer to online customer to today");
}

if ($_GET['EmailType']=='TrackingConfirmation'){
	// change status of the order in Opencart, as complete = 5
	include ('includes/WeberpOpenCartDefines.php');
	include ('includes/OpenCartGeneralFunctions.php');
	include ('includes/OpenCartConnectDB.php');
	UpdateOpenCartOrderStatus($_GET['CustomerOrder'], 5, $db_oc, $oc_tableprefix);
	prnMsg("Updated Order status in OpenCart as COMPLETE");

	// update the sales order, as we send shipped it
	$sql = "UPDATE salesorders 
			SET klemailtrackingconfirm = '" . Date('Y-m-d') . "'
			WHERE orderno =	'" . $_GET['TransNo'] . "'";
	$ErrMsg =_('Could not update the sales order KL email remind bank transfer date because');
	$result = DB_query($sql,$ErrMsg);
	prnMsg("Updated date of sending remind bank transfer to online customer to today");
}

if (($_GET['EmailType']=='ThankYouOrder') OR ($_GET['EmailType']=='NoSendThankYou')){
	$sql = "UPDATE salesorders 
			SET klemailthankyouorder = '" . Date('Y-m-d') . "'
			WHERE orderno =	'" . $_GET['TransNo'] . "'";
	$ErrMsg =_('Could not update the sales order KL email thank you date because');
	$result = DB_query($sql,$ErrMsg);
	prnMsg("Updated date of sending thank you! to online customer to today");
}

include('includes/footer.php');

function ShowBankDetails ($Currency, $OrderNo) {

	if ($Currency == 'IDR'){
		$BankName = 'Bank Mandiri';
		$BankBeneficiary = 'PT Bumi Biru';
		$AccountNumber = '1450011000102';
		$SwiftCode = 'BMRIIDJA';
	}else{
		$BankName = 'Bank Mandiri';
		$BankBeneficiary = 'PT Bumi Biru';
		$AccountNumber = '1450011000102';
		$SwiftCode = 'BMRIIDJA';
	} 
	$Showresult = '<table border="1">
				<tr>
					<th colspan="2"><strong>' . _('Bank Account Details') . '</strong></th>
				</tr>
				<tr>
					<td>' . _('Bank') .':</td><td>' . $BankName . '</td>
				</tr>
				<tr>
					<td>' . _('Account Holder Name') . ':</td><td>' . $BankBeneficiary . '</td>
				</tr>
				<tr>
					<td>' . _('Account Number') . ':</td><td>' . $AccountNumber . '</td>
				</tr>';
	if($SwiftCode != ''){
		$Showresult .= '<tr>
						<td>' . _('SWIFT Code') . ':</td><td>' . $SwiftCode . '</td>
					</tr>';
	}			
	$Showresult .= '<tr>
					<td>' . _('Reference') . ':</td><td>'  . _('Online Order: ') . $OrderNo . '</td>
			</tr>
			</table>';
	return $Showresult;
}


?>

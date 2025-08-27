<?php

require(__DIR__ . '/includes/session.php');

$Title = __('KL Follow Up Email System');
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');

//Get Out if we have no order number to work with
If (!isset($_GET['TransNo']) OR $_GET['TransNo']==''){
	prnMsg( __('We need an order number to send an email to online customer') , 'error');
	include('includes/footer.php');
	exit();
}
If (!isset($_GET['EmailType']) OR $_GET['EmailType']==''){
	prnMsg( __('We need an email type to send an email to online customer') , 'error');
	include('includes/footer.php');
	exit();
}

if ($_GET['EmailType']!='NoSendThankYou'){
	// if we send an email different than "no send thank you email", we have to prepare the mail text
	$Headers = "From: " . $_SESSION['ShopName'] . " <" . strip_tags($_SESSION['ShopManagerEmail']) . ">\r\n";
	$Headers .= "Reply-To: " . $_SESSION['ShopName'] . " <". strip_tags($_SESSION['ShopManagerEmail']) . ">\r\n";
	$Headers .= "Cc: " . $_SESSION['ShopName'] . " <". strip_tags($_SESSION['ShopManagerEmail']) . ">\r\n";
	$Headers .= "MIME-Version: 1.0\r\n";
	$Headers .= "Content-Type: text/html; charset=utf-8\r\n";

	if ($_GET['EmailType']=='NoOrderPlaced'){
		$MailSubject = $_SESSION['ShopName'] . ' ' . __('Customer Registration');
		$SQL = "SELECT debtorsmaster.debtorno,
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
		$MailSubject = $_SESSION['ShopName'] . ' ' . __('Bank Transfer Confirmation needed for Order') . ': ' . locale_number_format($_GET['CustomerOrder'],0) . ' (' . locale_number_format($_GET['TransNo'],0) . ')';
		$SQL = "SELECT salesorders.debtorno,
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
		$MailSubject = $_SESSION['ShopName'] . ' ' . __('Payment Confirmation for Order') . ': ' . locale_number_format($_GET['CustomerOrder'],0) . ' (' . locale_number_format($_GET['TransNo'],0) . ')';
		$SQL = "SELECT salesorders.debtorno,
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
				AND (debtortrans.type = 12 AND salesorders.klemailtrackingconfirm = '1000-01-01') 
				AND salesorders.orderno='" . $_GET['TransNo'] . "'";
		
	}elseif ($_GET['EmailType']=='TrackingConfirmation'){
		$MailSubject = $_SESSION['ShopName'] . ' ' . __('Shipment Confirmation for Order') . ': ' . locale_number_format($_GET['CustomerOrder'],0) . ' (' . locale_number_format($_GET['TransNo'],0) . ')';
		$SQL = "SELECT salesorders.debtorno,
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
		$MailSubject = $_SESSION['ShopName'] . ' ' . __(' Thank You!') ;
		$SQL = "SELECT salesorders.debtorno,
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


	$ErrMsg = __('There was a problem retrieving the order header details for Order Number') . ' ' . $_GET['TransNo'] . ' ' . __('from the database');
	$Result=DB_query($SQL, $ErrMsg);

	//If there are no rows, there's a problem.
	if (DB_num_rows($Result)==0){
		prnMsg( __('Unable to Locate Order Number') . ' : ' . $_GET['TransNo'] . ' ', 'error');
		include('includes/footer.php');
		exit();
	} elseif (DB_num_rows($Result)==1){ /*There is only one order header returned - thats good! */
		$MyRow = DB_fetch_array($Result);
		$DeliverBlind = $MyRow['deliverblind'];
		$DeliveryDate = $MyRow['salesorders.deliverydate'];
	}else{
		prnMsg( __('Found too many Orders with Number') . ' : ' . $_GET['TransNo'] . ' ', 'error');
		include('includes/footer.php');
		exit();
	}
	
	$MailMessage = '
		<html>
		<head>
			<title>' .$MailSubject . '</title>
		</head>
		<body>';
	$Language = "ENGLISH";
	include('includes/KLFollowUpOnlineEmailMessageText.php');

	$Language = "BAHASA";
	include('includes/KLFollowUpOnlineEmailMessageText.php');

	$MailMessage .= '</body>
					</html>';

	// find email address "to" 
	if($_SESSION['ShopMode']=='test'){
		// do not bother customers if we are doing tests with their data
		$MailTo = $_SESSION['ShopManagerEmail'];
		$SendEmail = true;
	}else{
		$MailTo = $MyRow['contactemail'];
		$SendEmail = false;
	}

	// send the email
	// now don't send to customer as all communications handled by opencart
	// but we still want to  inform the team some lines below these
	if ($SendEmail){
		SendEmailFromWebERP($SysAdminEmail, 
							$MailTo,
							$MailSubject,
							$MailMessage,
							'',
							false);
		echo '<h1>Email sent to ' . $MailTo. '</h1><br />';
	}
	echo $MailMessage . "<br />";

}else{
	// No thanks email, so nothing had to be done.
	echo '<h1>NO Thank you Email for order ' . $_GET['TransNo'] . '</h1><br />';
}

if ($_GET['EmailType']=='NoOrderPlaced'){
	$SQL = "UPDATE debtorsmaster 
			SET klemailnowebshoporder = CURRENT_DATE
			WHERE debtorno =	'" . $_GET['TransNo'] . "'";
	$ErrMsg =__('Could not update the customers KL email no order placed date because');
	$Result = DB_query($SQL,$ErrMsg);
	prnMsg("Updated date of sending remind of no Online Shop order placed to today");
}

if ($_GET['EmailType']=='RemindBankTransfer'){
	$SQL = "UPDATE salesorders 
			SET klemailremindbanktransfer = CURRENT_DATE
			WHERE orderno =	'" . $_GET['TransNo'] . "'";
	$ErrMsg =__('Could not update the sales order KL email remind bank transfer date because');
	$Result = DB_query($SQL,$ErrMsg);
	prnMsg("Updated date of sending remind bank transfer to online customer to today");
}

if ($_GET['EmailType']=='PaymentConfirmation'){
	if($_SESSION['ShopMode']!='test'){
		// send a confirmation to team, so they prepare a new order ASAP, if it is NOT a test
		// now only for orders that are imported into webERP as quotation, payment processed in webERP manually and we don't want to bother the customer
		$MailTo = "kl-onlinesupport@kapal-laut.com";
		$MailSubject = "New order online. Process ASAP.";
		SendEmailFromWebERP($SysAdminEmail, 
							$MailTo,
							$MailSubject,
							$MailMessage,
							'',
							false);
	}
	// update the sales order, as we send the payment confirmation
	$SQL = "UPDATE salesorders 
			SET klemailpaymentconfirm = CURRENT_DATE
			WHERE orderno =	'" . $_GET['TransNo'] . "'";
	$ErrMsg =__('Could not update the sales order KL email payment confirmation date because');
	$Result = DB_query($SQL,$ErrMsg);
	prnMsg("Updated date of sending remind bank transfer to online customer to today");
}

if ($_GET['EmailType']=='TrackingConfirmation'){
	include('includes/KLDefines.php');
	include('includes/OCOpenCartGeneralFunctions.php');
	include('includes/OCOpenCartConnectDB.php');
	// change status of the order in Opencart, as OPENCART_ORDER_STATUS_SHIPPED
	$ReasonChangeStatusId = "webERP --> Order shipped via " . $MyRow['shippername'] . " AWB# = " . $MyRow['consignment'];  
	UpdateOpenCartOrderStatus($_GET['CustomerOrder'], OPENCART_ORDER_STATUS_SHIPPED, 1, $MyRow['shipvia'], $MyRow['consignment'], $ReasonChangeStatusId);
	prnMsg("Updated Order status in OpenCart as SHIPPED");
	// change status of the order in Opencart, as OPENCART_ORDER_STATUS_COMPLETE
	sleep(1); // to show the change of status in the correct order
	$ReasonChangeStatusId = "webERP --> Order shipped and accounted for.";
	UpdateOpenCartOrderStatus($_GET['CustomerOrder'], OPENCART_ORDER_STATUS_COMPLETE, 1, "", "", $ReasonChangeStatusId);
	prnMsg("Updated Order status in OpenCart as COMPLETE");

	// update the sales order, as we send shipped it
	$SQL = "UPDATE salesorders 
			SET klemailtrackingconfirm = CURRENT_DATE
			WHERE orderno =	'" . $_GET['TransNo'] . "'";
	$ErrMsg =__('Could not update the sales order KL email tracking confirm date because');
	$Result = DB_query($SQL,$ErrMsg);
	prnMsg("Updated date of sending remind bank transfer to online customer to today");
}

if (($_GET['EmailType']=='ThankYouOrder') OR ($_GET['EmailType']=='NoSendThankYou')){
	$SQL = "UPDATE salesorders 
			SET klemailthankyouorder = CURRENT_DATE
			WHERE orderno =	'" . $_GET['TransNo'] . "'";
	$ErrMsg =__('Could not update the sales order KL email thank you date because');
	$Result = DB_query($SQL,$ErrMsg);
	prnMsg("Updated date of sending thank you! to online customer to today");
}

include('includes/footer.php');

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
	}else{
		prnMsg( _('Found too many Orders with Number') . ' : ' . $_GET['TransNo'] . ' ', 'error');
		include('includes/footer.php');
		exit;
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
		$SendEmail = TRUE;
	}else{
		$MailTo = $myrow['contactemail'];
		$SendEmail = FALSE;
	}

	// send the email
	// now don't send to customer as all communications handled by opencart
	// but we still want to  inform the team some lines below these
	if ($SendEmail){
		if($_SESSION['SmtpSetting']==0){
			$result = mail( $MailTo, $MailSubject, $MailMessage, $headers );
		}else{
			include('includes/htmlMimeMail.php');
			$mail = new htmlMimeMail();
			$mail->setSubject($mailSubject);
			$mail->setHTML($MailMessage);
			$result = SendmailBySmtp($mail,array($MailTo));
		}
		echo '<h1>Email sent to ' . $MailTo. '</h1><br />';
	}
	echo $MailMessage . "<br />";

}else{
	// No thanks email, so nothing had to be done.
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
		// now only for orders that are imported into webERP as quotation, payment processed in webERP manually and we don't want to bother the customer
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
	include ('includes/KLDefines.php');
	include ('includes/OpenCartGeneralFunctions.php');
	include ('includes/OpenCartConnectDB.php');
	// change status of the order in Opencart, as OPENCART_ORDER_STATUS_SHIPPED
	$ReasonChangeStatusId = "webERP --> Order shipped via " . $myrow['shippername'] . " AWB# = " . $myrow['consignment'];  
	UpdateOpenCartOrderStatus($_GET['CustomerOrder'], OPENCART_ORDER_STATUS_SHIPPED, 1, $myrow['shipvia'], $myrow['consignment'], $ReasonChangeStatusId, $db, $db_oc);
	prnMsg("Updated Order status in OpenCart as SHIPPED");
	// change status of the order in Opencart, as OPENCART_ORDER_STATUS_COMPLETE
	sleep(1); // to show the change of status in the correct order
	$ReasonChangeStatusId = "webERP --> Order shipped and accounted for.";
	UpdateOpenCartOrderStatus($_GET['CustomerOrder'], OPENCART_ORDER_STATUS_COMPLETE, 1, "", "", $ReasonChangeStatusId, $db, $db_oc);
	prnMsg("Updated Order status in OpenCart as COMPLETE");

	// update the sales order, as we send shipped it
	$sql = "UPDATE salesorders 
			SET klemailtrackingconfirm = '" . Date('Y-m-d') . "'
			WHERE orderno =	'" . $_GET['TransNo'] . "'";
	$ErrMsg =_('Could not update the sales order KL email tracking confirm date because');
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

///////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////
function ShowBankDetails ($Bank, $Currency, $OrderNo, $Language) {

	$BankBeneficiary = 'PT Bumi Biru';

	if ($Bank == 'bank_mandiri'){
		$BankName = 'Bank Mandiri';
		$AccountNumber = '14 50 01 10 00 102';
	}elseif($Bank == 'bank_bca'){
		$BankName = 'Bank BCA';
		$AccountNumber = '77 00 40 80 81';
	}elseif($Bank == 'bank_danamon'){
		$BankName = 'Bank Danamon';
		$AccountNumber = '35 68 00 55 02';
	}
	$ShowResult = ShowBankAccount($BankName, $BankBeneficiary, $AccountNumber, $SwiftCode, $OrderNo);
	return $ShowResult;
}

function ShowBankAccount($BankName, $BankBeneficiary, $AccountNumber, $SwiftCode, $OrderNo){
		if ($Language == "ENGLISH"){
		$TextBank0010 = 'Bank Account Details';
		$TextBank0020 = 'Bank Name';
		$TextBank0030 = 'Account Holder Name';
		$TextBank0040 = 'Account Number';
		$TextBank0050 = 'SWIFT Code';
		$TextBank0060 = 'Reference';
		$TextBank0070 = 'Online Order';
	}else{
		$TextBank0010 = 'Informasi Rekening Bank';
		$TextBank0020 = 'Nama Bank';
		$TextBank0030 = 'Nama Pemilik Rekening';
		$TextBank0040 = 'Nomor rekening';
		$TextBank0050 = 'Kode SWIFT';
		$TextBank0060 = 'Referensi';
		$TextBank0070 = 'Order Online';
	}	

	$ShowResult = '<table border="1">
				<tr>
					<th colspan="2"><strong>' . $TextBank0010 . '</strong></th>
				</tr>
				<tr>
					<td>' . $TextBank0020 .':</td><td>' . $BankName . '</td>
				</tr>
				<tr>
					<td>' . $TextBank0030 . ':</td><td>' . $BankBeneficiary . '</td>
				</tr>
				<tr>
					<td>' . $TextBank0040 . ':</td><td>' . $AccountNumber . '</td>
				</tr>';
	if($SwiftCode != ''){
		$ShowResult .= '<tr>
						<td>' . $TextBank0050 . ':</td><td>' . $SwiftCode . '</td>
					</tr>';
	}			
	$ShowResult .= '<tr>
					<td>' . $TextBank0060 . ':</td><td>'  . $TextBank0070 . ': ' . $OrderNo . '</td>
			</tr>
			</table>';
			
	return $ShowResult;
}

?>
<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.php');
$Title = _('KL Receipt Payment Online');
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLMarketplaceFunctions.php');
include('includes/OCOpenCartGeneralFunctions.php');
include('includes/OCOpenCartConnectDB.php');

//Get Out if we don't have the data needed to work with
if (!isset($_GET['OrderNo']) OR $_GET['OrderNo']==''){
	prnMsg( _('We need an order number to process the payment of online order') , 'error');
	include('includes/footer.php');
	exit;
}
if (!isset($_GET['PaymentCode']) OR $_GET['PaymentCode']==''){
	prnMsg( _('We need a payment code to process the payment of online order') , 'error');
	include('includes/footer.php');
	exit;
}
if (!isset($_GET['CustomerCode']) OR $_GET['CustomerCode']==''){
	prnMsg( _('We need a customer code to process the payment of online order') , 'error');
	include('includes/footer.php');
	exit;
}
if (!isset($_GET['Amount']) OR $_GET['Amount']==''){
	prnMsg( _('We need an amount to process the payment of online order') , 'error');
	include('includes/footer.php');
	exit;
}
if (($_GET['CustomerCode'] != "WEB-KL-IDR") 
	AND ($_GET['CustomerCode'] != "WEB-WH-IDR") 
	AND ($_GET['CustomerCode'] != "TOKOPEDIA") 
	AND ($_GET['CustomerCode'] != "LAZADA") 
	AND ($_GET['CustomerCode'] != "SHOPEE")){
	prnMsg( _('Script ready to process IDR online orders only') , 'error');
	include('includes/footer.php');
	exit;
}

$Result = ProcessPaymentOnlineOrder($_GET['OrderNo'], $_GET['PaymentCode'], $_GET['CustomerCode'], $_GET['Amount']);
						
if ($_GET['PaymentCode'] != "MANUAL_MARKETPLACE") {
	$TitleTable = "Process of online order payment";
}else{
	$TitleTable = "Mark the MarketPlace order as paid";
}
echo '<table class="selection"><tr><th colspan=2>' . $TitleTable . '</th></tr>';
echo '<tr><td>' . _('Order Number') . ':</td> <td>' . $_GET['OrderNo'] . '</td></tr>';
echo '<tr><td>' . _('Customer Code') . ':</td> <td>' . $_GET['CustomerCode'] . '</td></tr>';
echo '<tr><td>' . _('Total Amount') . ':</td> <td>' . number_format($_GET['Amount'],0) . ' ' . $Currency . '</td></tr>';
echo '<tr><td>' . _('Payment Code') . ':</td> <td>' . $_GET['PaymentCode'] . '</td></tr>';
echo '</table>';	//end of table of final show of order
	
include('includes/footer.php');

